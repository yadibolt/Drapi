<?php

namespace Drupal\drift_eleven\Core\HTTP;

interface ReplyInterface {
  /**
   * Constructs a new response used in Drift Eleven endpoints
   *
   * @param string|array $data - data to be processed and set
   * @param int $status - status of the response (valid HTTP status)
   * @param array $headers - additional headers
   */
  public function __construct(string|array $data, int $status = 200, array $headers = []);

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
