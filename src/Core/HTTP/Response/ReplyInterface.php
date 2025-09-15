<?php

namespace Drupal\drift_eleven\Core\HTTP\Response;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

interface ReplyInterface {
  public const string ACTION_INVALID_HEADER = 'invalid_header';
  public const string ACTION_INVALID_TOKEN = 'invalid_token';
  public const string ACTION_INVALID_OR_NOT_EXPIRED_TOKEN = 'invalid_or_not_expired_token';
  public const string ACTION_INVALID_PAYLOAD = 'invalid_payload';
  public const string ACTION_ALREADY_LOGGED_IN = 'already_logged_in';
  public const string ACTION_UNAUTHORIZED_ACCESS = 'unauthorized_access';
  public const string ACTION_SESSION_NOT_FOUND = 'session_not_found';
  public const string ACTION_USER_BLOCKED = 'user_blocked';
  public const string ACTION_COULD_NOT_INVALIDATE_SESSION = 'could_not_invalidate_session';
  public const string ACTION_COULD_NOT_CREATE_SESSION = 'could_not_create_session';
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
