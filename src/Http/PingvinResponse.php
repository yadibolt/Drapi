<?php

namespace Drupal\pingvin\Http;

use Drupal\pingvin\File\Retriever;
use Drupal\pingvin\Parser\RouteDocCommentParser;
use Drupal\pingvin\Route\Cache;
use Symfony\Component\HttpFoundation\Response;

class PingvinResponse extends Response {
  protected mixed $data;
  protected mixed $encodedContent;
  protected int $jsonDepth = 512;
  protected int $jsonFlags = 0;

  public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $ignoreCacheHandler = false) {
    parent::__construct('', $status, $headers);
    $this->setData($data);
    $this->statusCode = $status;

    if (!$ignoreCacheHandler) {
      $this->handleCreateCache();
    }

    $this->headers->remove('X-Generator');
    $this->headers->set('X-Generator', 'pingvin');
  }

  /**
   * @throws \ReflectionException
   * @throws \Exception
   */
  protected function handleCreateCache(): void {
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
      } catch (\Exception) { /* fails silently */ }
    }
  }

  protected function setData(string|array|null $data): void {
    $content = [];

    if ($data === null) {
      $this->data = [];
      $this->encodedContent = '{}';
      $this->headers->set('Content-Type', 'application/json');
      $this->setContent($this->encodedContent);
    }

    if (is_string($data) && json_validate($data, $this->jsonDepth, $this->jsonFlags)) {
      $data = json_decode($data, true, $this->jsonDepth, $this->jsonFlags);
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

    $encodedContent = json_encode($content, $this->jsonFlags, $this->jsonDepth);
    if (!$encodedContent) {
      $this->data = [];
      $this->encodedContent = '{}';
    } else {
      $this->data = $data;
      $this->encodedContent = $encodedContent;
    }

    $this->headers->set('Content-Type', 'application/json');
    $this->setContent($this->encodedContent);
  }

  protected function getCaller(): ?array {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    \Drupal::logger('pingvin')->info('@d', ['@d' => print_r($trace[3], true)]);
    return $trace[3] ?: null;
  }
}
