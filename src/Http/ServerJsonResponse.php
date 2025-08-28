<?php

namespace Drupal\pingvin\Http;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\pingvin\Middleware\Client\CorsMiddleware;
use Drupal\pingvin\Route\Route;
use Symfony\Component\HttpFoundation\Request;

class ServerJsonResponse extends CacheableJsonResponse {
  /**
   * Constructs a server JSON response.
   * Providing a data as array will result in custom response handling.
   * You can provide a 'message' key value pair to set the response message.
   *
   * Final response structure will be:
   * ```json
   * {
   *   "message": "Your message here",
   *   "error": true|false, - depends on the status code
   *   "timestamp": 1700000000,
   *   "data": null|array - your data
   * }
   *
   * @param mixed|null $data
   * @param int $status
   * @param ?Request $request
   * @param array $headers
   * @param bool $json
   */
  public function __construct(mixed $data = null, int $status = 200, ?Request $request = null, array $headers = [], bool $json = false) {
    parent::__construct($data, $status, $headers, $json);

    if (is_array($data)) {
      $_data = [];

      // we use 'actionId' as an identifier for client side
      // applications to give them a way to track the request
      // state.
      if (isset($data['actionId'])) {
        $_data['actionId'] = $data['actionId'];
        unset($data['actionId']);
      }

      if (isset($data['message'])) {
        $_data['message'] = $data['message'] ?: '';
        unset($data['message']);
      }

      $_data['error'] = $status >= 400;
      $_data['timestamp'] = time();
      if (!empty($data)) $_data['data'] = $data;

      $this->setData($_data);

      $this->headers->set('Access-Control-Allow-Origin', CorsMiddleware::ALLOW_ORIGINS);
      $this->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
      $this->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
      $this->headers->set('Access-Control-Expose-Headers', 'Content-Type, Authorization');
    }

    if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
      $compressed = gzencode($this->getContent(), 6);
      $this->setContent($compressed);
      $this->headers->set('Content-Encoding', 'gzip');
      $this->headers->set('Vary', 'Accept-Encoding');
    }

    if ($request !== null) {
      if ($request->headers->get('x-'.pw8dr1_PROJECT_ID.'-cacheable')) {
        \Drupal::logger('pingvin')->notice('Route cached.');
        $this->getCacheableMetadata()
          ->setCacheMaxAge(Route::CACHE_DURATION)
          ->setCacheContexts(['url.query_args'])
          ->setCacheTags([$request->headers->get('x-'.pw8dr1_PROJECT_ID.'-cacheable-context') ?: "pingvin"]);
      }
    }
  }
}
