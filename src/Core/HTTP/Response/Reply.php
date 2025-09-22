<?php

namespace Drupal\drift_eleven\Core\HTTP\Response;

use Drupal;
use Drupal\drift_eleven\Core\Cache\Cache;
use Drupal\drift_eleven\Core\Cache\CacheInterface;
use Drupal\drift_eleven\Core\Logger\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Reply extends Response implements ReplyInterface {
  protected const int JSON_DEPTH = 512;
  protected const int JSON_FLAGS = 0;
  protected array|string $data = [];
  protected bool $useCache = false;

  public function __construct(array|string $data, int $status = 200, array|ResponseHeaderBag $headers = [], bool $cached = false) {
    parent::__construct(
      $cached ? $data : '',
      $status,
      $cached ? (is_array($headers) ? $headers : $headers->all()) : []
    );

    if ($cached) {
      $this->headers->set('Cache-Control', 'public, max-age=' . CacheInterface::DURATION_DEFAULT . ', must-revalidate');
      $this->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + CacheInterface::DURATION_DEFAULT) . ' GMT');
      $this->headers->set('X-DriftEleven-Cache-Hit', 'HIT');
      $this->headers->set('X-DriftEleven-Cache', 'CACHED');
      $this->headers->set('Pragma', 'cache');
      $this->headers->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
      return;
    }

    /** @var Request $request */
    $request = Drupal::service('request_stack')->getCurrentRequest();
    $routeId = $request->attributes->get('_route');

    // check for cache option on route
    if ($routeId) {
      $config = Drupal::configFactory()->getEditable(D9M7_CONFIG_KEY);
      $routeRegistry = $config->get('routeRegistry') ?: [];

      if (isset($routeRegistry[$routeId])) {
        $route = $routeRegistry[$routeId];
        $this->useCache = $route['useCache'] ?: false;
      } else {
        $this->useCache = false;
      }
    } else {
      $this->useCache = false;
    }

    // headers
    $this->headers->set('Cache-Control', 'public, max-age=0, must-revalidate');
    $this->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + CacheInterface::DURATION_DEFAULT) . ' GMT');
    $this->headers->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
    if (strtolower($request->getMethod()) === 'get') {
      $this->headers->set('X-DriftEleven-Cache', 'CACHEABLE');
      $this->headers->set('X-DriftEleven-Cache-Hit', 'MISS');
      $this->headers->set('Pragma', 'no-cache');
    } else {
      $this->headers->set('X-DriftEleven-Cache', 'NOT-CACHEABLE');
    }
    $this->headers->set('Content-Type', 'application/json');

    if (!empty($headers) && is_array($headers)) $this->headers->replace($headers);
    if (!empty($headers) && $headers instanceof ResponseHeaderBag) $this->headers = $headers;

    // status
    $this->setStatusCode($status);

    // data
    $reshaped = $this->reshape($data);
    $this->setData($reshaped);

    // we cache the response if enabled on the route
    // only for GET requests
    if ($this->useCache && strtolower($request->getMethod()) === 'get') {
      $requestUri = $request->getPathInfo();
      $cacheName = D9M7_CACHE_KEY . ":url_$requestUri";
      $cacheTags = [];

      if (!empty($route['useMiddleware']) && in_array('auth', $route['useMiddleware'])) {
       $token = $request->headers->get('authorization');
        if ($token && preg_match('/^Bearer\s+(\S+)$/', $token, $matches)) $cacheName = D9M7_CACHE_KEY . ":token_" . $matches[1] . ":url_$requestUri";
      }

      if (!empty($route['cacheTags']) && is_array($route['cacheTags'])) {
        $cacheTags = $route['cacheTags'];
      }

      Cache::make($cacheName, [
        'data' => $this->data,
        'status' => $status,
        'headers' => $this->headers,
      ], CacheInterface::DURATION_DEFAULT, $cacheTags);

      // we also store the cache tags, so we can invalidate them later
      if (!empty($cacheTags)) {
        $storedCacheTags = Cache::find('cacheTags') ?: [];

        foreach ($cacheTags as $cacheTag) {
          if (!isset($storedCacheTags[$cacheTag])) $storedCacheTags[$cacheTag] = [];
          $storedCacheTags[$cacheTag][] = $cacheName;
        }

        Drupal::cache(CacheInterface::CACHE_BIN_KEY)->set('cacheTags', $storedCacheTags);
        Logger::l('Current cacheTags: @cacheTags', ['@cacheTags' => print_r($storedCacheTags, true)], 'info');
      }
    }
  }

  public function reshape(string|array $data): string {
    $shape = [];

    if (is_string($data) && json_validate($data, self::JSON_DEPTH, self::JSON_FLAGS)) {
      $data = json_decode($data, true, self::JSON_DEPTH, self::JSON_FLAGS);
    }

    if (isset($data['actionId'])) {
      $shape['actionId'] = $data['actionId'];
      unset($data['actionId']);
    }

    if (isset($data['message'])) {
      $shape['message'] = $data['message'] ?: '';
      unset($data['message']);
    }

    $shape['error'] = $this->statusCode >= 400;
    $shape['timestamp'] = time();

    if (!empty($data)) $shape['data'] = $data;

    return json_encode($shape, self::JSON_FLAGS, self::JSON_DEPTH) ?: "";
  }

  public function setData(string|array $data): void {
    $this->data = $data;
    $this->setContent($data);
  }
}
