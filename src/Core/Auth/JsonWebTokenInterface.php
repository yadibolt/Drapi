<?php

namespace Drupal\drift_eleven\Core\Auth;

interface JsonWebTokenInterface {
  /**
   * Expiration time for JWT access tokens in seconds.
   * This is set to 1 hour (3600 seconds) by default and is overridden in the configuration.
   * @var int
   */
  public const int ACCESS_EXP_TIME_DEFAULT = 3600;
  /**
   * Expiration time for JWT refresh tokens in seconds.
   * This is set to 1 week (604800 seconds) by default and is overridden in the configuration.
   * @var int
   */
  public const int REFRESH_EXP_TIME_DEFAULT = 604800;
  /**
   * Permanent expiration time for tokens
   * @var int
   */
  public const int EXP_TIME_PERMANENT_DEFAULT = 50 * 365 * 24 * 60 * 60 * 60;
  /**
   * Token type describing a refresh token
   * @var string
   */
  public const string TOKEN_REFRESH = 'refresh';
  /**
   * Token type describing an access token
   * @var string
   */
  public const string TOKEN_ACCESS = 'access';
  /**
   * A collection of token types
   * @var array
   */
  public const array TOKEN_TYPES = [
    self::TOKEN_REFRESH,
    self::TOKEN_ACCESS,
  ];

  /**
   * Generates a JWT token of the specified type with the given expiry time and additional data.
   *
   * @param string $tokenType type of token to create. Allowed values are: `access`, `refresh`
   * @param int $expiry expiration time in seconds (default is `ACCESS_EXP_TIME`). All possible values are: `ACCESS_EXP_TIME`, `REFRESH_EXP_TIME`
   * @param array $data additional data to include in the token payload
   * @return string the generated JWT token
   */
  public function make(string $tokenType, array $data = [], int $expiry = self::ACCESS_EXP_TIME_DEFAULT): string;
  /**
   * Validates JWT token and returns a response object with result
   *
   * @param string $token token to validate
   * @return JsonWebTokenResponse response object containing the result
   */
  public function validate(string $token): JsonWebTokenResponse;

  /**
   * Validates the expiry and returns appropriate response
   *
   * @param string $token token to verify expiry of
   * @return bool return true if the token is expired, else false
   */
  public function isExpired(string $token): bool;

  /**
   * Returns a payload from token if exists
   *
   * @param string $token token to get payload from
   * @return ?array payload of the token or null if the payload is not parsable
   */
  public static function payloadFrom(string $token): ?array;
}
