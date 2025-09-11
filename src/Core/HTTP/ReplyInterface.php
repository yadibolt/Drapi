<?php

namespace Drupal\drift_eleven\Core\HTTP;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

interface ReplyInterface {
  /**
   * Constructs a new response used in Drift Eleven endpoints
   *
   * @param string|array $data - data to be processed and set
   * @param int $status - status of the response (valid HTTP status)
   * @param array|ResponseHeaderBag $headers - additional headers
   * @param bool $cached - whether send the response as cached (used internally)
   */
  public function __construct(string|array $data, int $status = 200, array|ResponseHeaderBag $headers = [], bool $cached = false);

  /**
   * Reshapes an associative array, so it follows
   * the Drift Eleven response structure
   *
   * @param string|array $data -
   * @return string
   */
  public function reshape(string|array $data): string;

  /**
   * Sets the response data
   *
   * @param string|array $data
   * @return void
   */
  public function setData(string|array $data): void;
}
