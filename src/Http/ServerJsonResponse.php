<?php

namespace Drupal\pingvin\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class ServerJsonResponse extends JsonResponse {
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
   * @param array $headers
   * @param bool $json
   */
  public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $json = false) {
    parent::__construct($data, $status, $headers, $json);

    if (is_array($data)) {
      $_data = [];

      if (isset($data['message'])) {
        $_data['message'] = $data['message'] ?: '';
        unset($data['message']);
      }

      $_data['error'] = $status >= 400;
      $_data['timestamp'] = time();
      if (!empty($data)) $_data['data'] = $data;

      $this->setData($_data);
    }
  }
}
