<?php

namespace Drupal\drift_eleven\Core\Auth;

interface JsonWebTokenResponseInterface {
  /**
   * Prefix used in $action responses
   * @var string
   */
  public const string ACTION_PREFIX = 'token:';
  /**
   * Action describing invalid format
   * @string
   */
  public const string ACTION_INVALID_FORMAT = 'invalid_format';
  /**
   * Action describing expiration
   * @string
   */
  public const string ACTION_EXPIRED = 'expired';
  /**
   * Action describing invalid token
   * @string
   */
  public const string ACTION_INVALID = 'invalid';
  /**
   * Action describing success
   * @string
   */
  public const string ACTION_OK = 'ok';
  /**
   * A collection of action responses
   * @array
   */
  public const array ACTION_TYPES = [
    self::ACTION_OK,
    self::ACTION_INVALID_FORMAT,
    self::ACTION_EXPIRED,
    self::ACTION_INVALID,
    self::ACTION_OK,
  ];
  /**
   * Constructs a JsonWebTokenResponse object
   *
   * @param string $action actionId of the response
   * @param bool $valid if the response should be valid
   * @param bool $expired if the response should be expired
   * @param bool $error if the response contains error
   */
  public function __construct(string $action, bool $valid, bool $expired, bool $error = false);
}
