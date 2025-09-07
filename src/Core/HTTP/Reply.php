<?php

namespace Drupal\drift_eleven\Core\HTTP;

use Drupal;
use Drupal\drift_eleven\Core\Cache\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Reply extends Response implements ReplyInterface {
  protected const int JSON_DEPTH = 512;
  protected const int JSON_FLAGS = 0;

  protected array|string $data = [];
  protected bool $useCache = false;

  public function __construct(array|string $data, int $status = 200, array $headers = []) {
    parent::__construct('', $status, $headers);

    /** @var Request $request */
    $request = Drupal::service('request_stack')->getCurrentRequest();
    $requestUri = $request->getRequestUri();

    // todo set cached
    $this->useCache = true;

    if ($this->useCache) {
      $cachedData = Cache::find("drift_eleven:url:$requestUri");
      if (!empty($cachedData)) {
        // we found cached data, set the content instantly
        $this->setContent($cachedData['content']['data']);
        $this->setStatusCode($cachedData['content']['status']);
        $this->headers->replace($cachedData['content']['headers']);

        return;
      }
    }

    // no cached data found, we create new cache record
    // if $useCache is set to true
    if (!empty($headers)) $this->headers->replace($headers);
    $this->setStatusCode($status);
    $reshaped = $this->reshape($data);
    $this->setData($reshaped);

    if ($this->useCache) Cache::make("drift_eleven:url:$requestUri", [
      'data' => $this->data,
      'status' => $status,
      'headers' => $this->headers,
    ]);
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
