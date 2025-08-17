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
  protected const string JWT_TOKEN_TYPE_ACCESS = 'access';
  /**
   * Refresh token type that is used to obtain new access tokens and extend user sessions.
   *
   * @var string
   */
  protected const string JWT_TOKEN_TYPE_REFRESH = 'refresh';
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
    $config = Drupal::configFactory()->getEditable(pw8dr1_PROJECT_ID);
    $this->secret = $config->get('jwt_secret') ?: pw8dr1_PROJECT_ID; // TODO: change

    if ($this->secret === pw8dr1_PROJECT_ID) {
      Drupal::logger(pw8dr1_PROJECT_ID)->warning('JWT secret key is not set in the configuration. Using default value!');
    }
  }

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

    if (!$valid) $action = 'invalid';
    if ($expired) $action = 'expired';
    else $action = 'ok';

    return $this->jwtResponse(
      $action,
      $valid,
      $expired,
    );
  }

  protected function verifyExpiry(string $token): bool {
    $payload = self::getPayload($token);

    return (isset($payload['exp']) && $payload['exp'] > time());
  }

  public static function getPayload(string $token) {
    [, $payload,] = explode('.', $token);
    $payload = Base64::decode($payload);

    return json_decode($payload, true);
  }

  protected function jwtResponse(string $action, bool $valid, bool $expired, bool $error = false): array {
    return [
      'action' => 'token:'. $action,
      'valid' => $valid,
      'expired' => $expired,
      'error' => $error,
      'timestamp' => time(),
    ];
  }
}
