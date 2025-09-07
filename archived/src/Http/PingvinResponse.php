<?php

namespace Drupal\pingvin\Http;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\pingvin\File\Retriever;
use Drupal\pingvin\Parser\RouteDocCommentParser;
use Drupal\pingvin\Route\Route;

class PingvinResponse extends CacheableJsonResponse {
  protected mixed $data;
  protected mixed $encodedContent;
  protected const int JSON_DEPTH = 512;
  protected const int JSON_FLAGS = 0;
  protected bool $useCache = false;

  public function __construct(mixed $data, int $status = 200, array $headers = [], bool $forceUseCache = false) {
    parent::__construct('', $status, $headers);

    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $url = $request->getRequestUri();

    $this->setUseCache($forceUseCache);
    $this->useModuleDataScheme($data);

    $e = \Drupal::cache('pingvin')->get('pingvin_url');
    if (!$e) {
      \Drupal::cache('pingvin')->set('pingvin_url', $this->getContent(), time() + 100);
      \Drupal::logger('pingvin')->notice('Pingvin Response OK @d', ['@d' => print_r(\Drupal::cache('pingvin')->get('pingvin_url'), true)]);
    }
  }

  public function useModuleDataScheme(mixed $data): void {
    $content = [];

    if ($data === null) return;

    if (is_string($data) && json_validate($data, self::JSON_DEPTH, self::JSON_FLAGS)) {
      $data = json_decode($data, true, self::JSON_DEPTH, self::JSON_FLAGS);
    }

    if (isset($data['actionId'])) {
      $content['actionId'] = $data['actionId'];
      unset($data['actionId']);
    }

    if (isset($data['message'])) {
      $content['message'] = $data['message'] ?: '';
      unset($data['message']);
    }

    $content['error'] = $this->statusCode >= 400;
    $content['timestamp'] = time();

    if (!empty($data)) $content['data'] = $data;

    $this->setData($content);
  }

  /**
   * @throws \ReflectionException
   */
  public function setUseCache(bool $forceUseCache): void {
    if ($forceUseCache) {
      $this->useCache = true;
    }

    $caller = $this->getResponseCallerClass();
    if (isset($caller['class']) && isset($caller['function'])) {
      if (!in_array(strtolower($caller['function']), Route::ALLOWED_ROUTE_METHODS)) return;

      $reflection = new \ReflectionClass($caller['class']);
      $filePath = $reflection->getFileName();

      try {
        $content = new Retriever($filePath)->retrieve('docComment');
        if ($content) {
          $routeContent = new RouteDocCommentParser($content)->parse(true);
          if ($routeContent['cacheable']) $this->useCache = true;
        }
      } catch (\Exception) {}
    }
  }

  public function getResponseCallerClass() {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    return $trace[3] ?: null;
  }


  /*public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $cached = false) {
    parent::__construct('', $status, $headers);
    $this->cached = $cached;
    $this->statusCode = $status;
    $this->setData($data);

    \Drupal::logger('pingvin')->notice('Pingvin Response OK @d', ['@d' => print_r($this->data, true)]);

    if (!$cached) {
      $this->handleCreateCache();
    }

    $this->headers->remove('X-Generator');
    $this->headers->set('X-Generator', 'pingvin');
  }*/

  /**
   * @throws \ReflectionException
   * @throws \Exception
   */
  /*protected function handleCreateCache(): void {
    // todo: depending on caller, set attribute route - cacheable
    $caller = $this->getCaller();

    if (isset($caller['class'])) {
      $ref = new \ReflectionClass($caller['class']);
      $filePath = $ref->getFileName();

      try {
        $content = new Retriever($filePath)->retrieve('docComment');
        if ($content) {
          $routeContent = new RouteDocCommentParser($content)->parse(true);

          if ($routeContent['cacheable']) {
            // $this->headers->set('Cache-Control', 'private');
            $this->headers->set('x-pingvin-cache', 'CACHEABLE');
          } else {
            $this->headers->set('x-pingvin-cache', 'UNCACHEABLE');
          }
        }
      } catch (\Exception) {  }
    }
  }*/

  /*public function setData(string|array|null $data): void {
    $content = [];

    if ($this->cached) {
      $this->data = $data;
      $this->setContent($data);

      \Drupal::logger('pingvin')->notice('RETUUUURN @d', ['@d' => print_r($this->data, true)]);
      return;
    }

    if ($data === null) {
      $this->data = [];
      $this->encodedContent = '{}';
      $this->headers->set('Content-Type', 'application/json');
      $this->setContent($this->encodedContent);
    }

    if (is_string($data) && json_validate($data, self::JSON_DEPTH, self::JSON_FLAGS)) {
      $data = json_decode($data, true, self::JSON_DEPTH, self::JSON_FLAGS);
    }

    \Drupal::logger('pingvin')->notice('AAAAAAAAAAA @d', ['@d' => print_r($data, true)]);

    if (isset($data['actionId'])) {
      $content['actionId'] = $data['actionId'];
      unset($data['actionId']);
    }

    if (isset($data['message'])) {
      $content['message'] = $data['message'] ?: '';
      unset($data['message']);
    }

    $content['error'] = $this->statusCode >= 400;
    $content['timestamp'] = time();

    if (!empty($data)) $content['data'] = $data;

    $encodedContent = json_encode($content, self::JSON_FLAGS, self::JSON_DEPTH);
    if (!$encodedContent) {
      $this->data = [];
      $this->encodedContent = '{}';
    } else {
      $this->data = $data;
      $this->encodedContent = $encodedContent;
    }

    $this->headers->set('Content-Type', 'application/json');
    $this->setContent($this->encodedContent);
  }*/

  /*protected function getCaller(): ?array {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    \Drupal::logger('pingvin')->info('@d', ['@d' => print_r($trace[3], true)]);
    return $trace[3] ?: null;
  }*/

  /*public static function fromCache(string $content): array {
    if (json_validate($content, self::JSON_DEPTH, self::JSON_FLAGS)) {
      $content = json_decode($content, true, self::JSON_DEPTH, self::JSON_FLAGS);

      $data = $content['data'] ?: [];

      // unset automatically gen. data
      if (isset($content['data'])) unset($content['data']);
      if (isset($content['error'])) unset($content['error']);
      if (isset($content['timestamp'])) unset($content['timestamp']);
      // set attrs
      if (isset($content['message'])) $data['message'] = $content['message'];
      if (isset($content['actionId'])) $data['actionId'] = $content['actionId'];

      return $data;
    }

    return [];
  }*/
}
