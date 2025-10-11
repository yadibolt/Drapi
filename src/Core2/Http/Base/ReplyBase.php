<?php

namespace Drupal\drift_eleven\Core2\Http\Base;

use Drupal;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ReplyBase extends Response {
  protected const int DEPTH = 512;
  protected const int FLAGS = 0;

  protected array|string $data = [];
  protected bool $responseCached = false;
  protected bool $cacheable = false;

  public function __construct(array|string $data, int $status = 200, array|ResponseHeaderBag $headers = []) {
    if (!is_array($headers) && $headers instanceof ResponseHeaderBag) {
      if ($headers->get(HTTP_HEADER_CACHE_NAME_DEFAULT) === HTTP_HEADER_CACHED_DEFAULT) {
        if ($headers->get(HTTP_HEADER_CACHE_HIT_NAME_DEFAULT) &&
          $headers->get(HTTP_HEADER_CACHE_HIT_NAME_DEFAULT) === HTTP_HEADER_CACHE_HIT_DEFAULT) {
          $this->responseCached = true;
        }
      }
    }

    parent::__construct(
      content: $this->responseCached ? $data : '',
      status: $status,
      headers: $this->responseCached ? (is_array($headers) ? $headers : $headers->all()) : []
    );

    // preset useCache

    $this->setHeaders();
  }

  protected function setUseCacheByRoute(): void {
    $request = Drupal::service('request_stack')->getCurrentRequest();
    $routeId = $request->attributes->get('_route');


  }

  protected function setHeaders(): void {
    $this->headers->set('Content-Type', 'application/json');

    // cache hit
    if ($this->responseCached) {
      $this->headers->set(HTTP_HEADER_CACHEABLE_NAME_DEFAULT, HTTP_HEADER_CACHEABLE_DEFAULT);
      $this->headers->set(HTTP_HEADER_CACHE_NAME_DEFAULT, HTTP_HEADER_CACHED_DEFAULT);
      $this->headers->set(HTTP_HEADER_CACHE_HIT_NAME_DEFAULT, HTTP_HEADER_CACHE_HIT_DEFAULT);
      $this->headers->set('Pragma', 'cache');
      $this->headers->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
      return;
    }

    // cache did not hit but is cacheable
    if ($this->cacheable) {
      $this->headers->set('Cache-Control', 'public, max-age=0, must-revalidate');
      $this->headers->set(HTTP_HEADER_CACHEABLE_NAME_DEFAULT, HTTP_HEADER_CACHEABLE_DEFAULT);
      $this->headers->set(HTTP_HEADER_CACHE_NAME_DEFAULT, HTTP_HEADER_NOT_CACHED_DEFAULT);
      $this->headers->set(HTTP_HEADER_CACHE_HIT_NAME_DEFAULT, HTTP_HEADER_CACHE_MISS_DEFAULT);
      $this->headers->set('Pragma', 'no-cache');
      $this->headers->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
      return;
    }

    // not cacheable
    $this->headers->set('Cache-Control', 'public, max-age=0, must-revalidate');
    $this->headers->set(HTTP_HEADER_CACHEABLE_NAME_DEFAULT, HTTP_HEADER_NOT_CACHEABLE_DEFAULT);
    $this->headers->set(HTTP_HEADER_CACHE_NAME_DEFAULT, HTTP_HEADER_NOT_CACHED_DEFAULT);
    $this->headers->set(HTTP_HEADER_CACHE_HIT_NAME_DEFAULT, HTTP_HEADER_CACHE_MISS_DEFAULT);
    $this->headers->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
    $this->headers->set('Pragma', 'no-cache');
  }
}
