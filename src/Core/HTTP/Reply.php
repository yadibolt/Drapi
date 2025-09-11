<?php

namespace Drupal\drift_eleven\Core\HTTP;

use Drupal;
use Drupal\drift_eleven\Core\Cache\Cache;
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

      Cache::make(D9M7_CACHE_KEY . ":url:$requestUri", [
        'data' => $this->data,
        'status' => $status,
        'headers' => $this->headers,
      ]);
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
