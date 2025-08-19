<?php

namespace Drupal\pingvin\Auth;

use Drupal;
use Drupal\pingvin\Util\Base64;
use Exception;

class JsonWebToken {
  /**
   * Algorithm used for signing JWT tokens.
   *
   * @var string
   */
  protected const string JWT_HASH_ALGORITHM = 'sha512';
  /**
   * Expiration time for JWT access tokens in seconds.
   *
   * @var int
   */
  protected const int JWT_ACCESS_TOKEN_EXP_TIME = 3600;
  /**
   * Expiration time for JWT refresh tokens in seconds.
   *
   * @var int
   */
  protected const int JWT_REFRESH_TOKEN_EXP_TIME = 604800;
  /**
   * Access token type that is used to validate user actions and permissions/roles.
   *
   * @var string
   */
  public const string JWT_TOKEN_TYPE_ACCESS = 'access';
  /**
   * Refresh token type that is used to obtain new access tokens and extend user sessions.
   *
   * @var string
   */
  public const string JWT_TOKEN_TYPE_REFRESH = 'refresh';
  /**
   * Secret key used for signing JWT tokens.
   *
   * @var string
   */
  protected string $secret;

  /**
   * Constructor for the JsonWebToken class.
   *
   * Initializes the JWT secret key from the configuration.
   * If the secret key is not set, it uses a default value and logs a warning.
   */
  public function __construct() {
    $configCtx = pw8dr1_PROJECT_ID . '.settings';
    $config = Drupal::configFactory()->getEditable($configCtx);
    $this->secret = $config->get('jwt_secret') ?: pw8dr1_PROJECT_ID; // TODO: change

    if ($this->secret === pw8dr1_PROJECT_ID) {
      Drupal::logger(pw8dr1_PROJECT_ID)->warning('JWT secret key is not set in the configuration. Using default value!');
    }
  }

  /**
   * Creates a JWT token of the specified type with optional data.
   *
   * @param string $tokenType
   * @param array $data
   * @return string
   * @throws Exception
   */
  public function create(string $tokenType, array $data = []): string {
    if (!in_array($tokenType, [self::JWT_TOKEN_TYPE_ACCESS, self::JWT_TOKEN_TYPE_REFRESH])) {
      throw new Exception('Invalid token type. Allowed types are: ' . self::JWT_TOKEN_TYPE_ACCESS . ' and ' . self::JWT_TOKEN_TYPE_REFRESH);
    }

    // set the expiration time based on the token type
    $expirationTime = $tokenType === self::JWT_TOKEN_TYPE_ACCESS
      ? self::JWT_ACCESS_TOKEN_EXP_TIME
      : self::JWT_REFRESH_TOKEN_EXP_TIME;

    // base header used for all JWT tokens
    $jwt = [
      pw8dr1_PROJECT_ID => hash('sha256', time()), // unique identifier for the token
      'iss' => pw8dr1_PROJECT_NAME,
      'iat' => time(),
      'exp' => time() + $expirationTime,
    ];

    // additional data if the token is not a refresh token
    // and the data is present
    if (!empty($data) && $tokenType === self::JWT_TOKEN_TYPE_ACCESS) $jwt['data'] = $data;

    $header = Base64::encode(json_encode(["alg" => "HS512", "typ" => "JWT"]));
    $payload = Base64::encode(json_encode($jwt));
    $sig = Base64::encode(hash_hmac(self::JWT_HASH_ALGORITHM, $header . '.' . $payload, $this->secret, true));

    // return the token in the format: header.payload.signature
    return $header . '.' . $payload . '.' . $sig;
  }

  /**
   * Verifies the JWT token and returns its status.
   * Returned array follows standardized format.
   * The array contains the following keys:
   * - actionId: token:invalid_format, token:invalid, token:expired, token:ok
   *
   * @param string $token
   * @return array
   */
  public function verify(string $token): array {
    if (!str_contains($token, '.')) {
      return $this->jwtResponse(
        'invalid_format',
        false,
        false,
        true,
      );
    }

    [$header, $payload, $sig] = explode('.', $token);

    $sig = Base64::decode($sig);
    $expSig = hash_hmac(self::JWT_HASH_ALGORITHM, $header . '.' . $payload, $this->secret, true);

    $valid = hash_equals($sig, $expSig);
    $expired = $this->verifyExpiry($token);

    if (!$valid) $actionId = 'invalid';
    if ($expired) $actionId = 'expired';
    else $actionId = 'ok';

    return $this->jwtResponse(
      $actionId,
      $valid,
      $expired,
    );
  }

  /**
   * Verifies if the JWT token is expired.
   *
   * @param string $token
   * @return bool
   */
  protected function verifyExpiry(string $token): bool {
    $payload = self::getPayload($token);

    return (isset($payload['exp']) && $payload['exp'] > time());
  }

  /**
   * Extracts the payload from the JWT token.
   *
   * @param string $token
   * @return array
   */
  public static function getPayload(string $token): array {
    [, $payload,] = explode('.', $token);
    $payload = Base64::decode($payload);

    return json_decode($payload, true);
  }

  /**
   * Generates a standardized JWT response.
   *
   * @param string $actionId
   *    The action performed on the token (e.g., 'create', 'verify').
   * @param bool $valid
   *    Indicates if the token is valid.
   * @param bool $expired
   *    Indicates if the token is expired.
   * @param bool $error
   *    Indicates if there was an error during processing.
   *
   * @return array
   *   The response array containing the action, validity, expiration status, and error flag.
   */
  protected function jwtResponse(string $actionId, bool $valid, bool $expired, bool $error = false): array {
    return [
      'actionId' => 'token:'. $actionId,
      'valid' => $valid,
      'expired' => $expired,
      'error' => $error,
      'timestamp' => time(),
    ];
  }
}
