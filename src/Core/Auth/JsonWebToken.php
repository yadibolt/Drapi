<?php

namespace Drupal\drift_eleven\Core\Auth;

use Drupal;
use Drupal\drift_eleven\Core\Logger\Logger;
use Drupal\drift_eleven\Core\Logger\LoggerInterface;
use Drupal\drift_eleven\Core\Util\Base64;
use InvalidArgumentException;

class JsonWebToken implements JsonWebTokenInterface {
  /**
   * Algorithm used for signing JWT tokens.
   * @var string
   */
  protected const string HASH_ALGORITHM = 'sha512';
  /**
   * Secret used to create a JWT Token. Defaults to the name of the module.
   * It can and SHOULD be replaced by the configuration option.
   * @var string
   */
  protected string $secret;
  /**
   * Expiration time for JWT refresh tokens in seconds.
   * This is set to 1 week (604800 seconds) by default and is overridden in the configuration.
   * @var int
   */
  protected int $refreshExpTime;
  /**
   * Expiration time for JWT access tokens in seconds.
   * This is set to 1 hour (3600 seconds) by default and is overridden in the configuration.
   * @var int
   */
  protected int $accessExpTime;

  public function __construct() {
    $config = Drupal::configFactory()->getEditable(D9M7_CONFIG_KEY);
    $this->secret = $config->get('jwtSecret') ?: D9M7_PROJECT_ID;
    $this->accessExpTime = $config->get('jwtAccessExpTime') ?: self::ACCESS_EXP_TIME_DEFAULT;
    $this->refreshExpTime = $config->get('jwtRefreshExpTime') ?: self::REFRESH_EXP_TIME_DEFAULT;

    if ($this->secret === D9M7_PROJECT_ID) {
      Logger::l("JWT Token is set to default. You should immediately change the secret key in the configuration!", [], LoggerInterface::LEVEL_WARNING);
    }
  }

  public function make(string $tokenType, array $data = [], int $expiry = self::ACCESS_EXP_TIME_DEFAULT): string {
    if (!in_array($tokenType, self::TOKEN_TYPES)) throw new InvalidArgumentException('You\'ve provided a wrong token type.');

    $expTime = match ($tokenType) {
      self::TOKEN_ACCESS => $this->accessExpTime,
      self::TOKEN_REFRESH => $this->refreshExpTime,
    };

    if (!empty($expiry) && $expiry === self::ACCESS_EXP_TIME_DEFAULT) $expTime = self::ACCESS_EXP_TIME_DEFAULT;

    $moduleTokenId = D9M7_PROJECT_ID . '_' .hash('sha256', $expTime + time());
    $payload = [
      D9M7_PROJECT_ID => $moduleTokenId,
      'iss' => D9M7_PROJECT_NAME,
      'iat' => time(),
      'exp' => time() + $expTime,
    ];

    if (!empty($data) && $tokenType === self::TOKEN_ACCESS) $payload['data'] = $data;

    $tokHeader = Base64::encode(json_encode(["alg" => "HS512", "typ" => "JWT"]));
    $tokPayload = Base64::encode(json_encode($payload));
    $tokSig = Base64::encode(hash_hmac(self::HASH_ALGORITHM, $tokHeader . '.' . $tokPayload, $this->secret, true));

    return $tokHeader . '.' . $tokPayload . '.' . $tokSig;
  }

  public function validate(string $token): JsonWebTokenResponse
  {
    if (empty($token) || !str_contains($token, '.')) return new JsonWebTokenResponse(
      JsonWebTokenResponseInterface::ACTION_INVALID_FORMAT,
      false, false, true
    );

    [$tokHeader, $tokPayload, $tokSig] = explode('.', $token);

    $sig = Base64::decode($tokSig);
    $expSig = hash_hmac(self::HASH_ALGORITHM, $tokHeader . '.' . $tokPayload, $this->secret, true);

    $valid = hash_equals($sig, $expSig);
    $expired = $this->isExpired($token);

    $action = JsonWebTokenResponseInterface::ACTION_OK;
    if (!$valid) $action = JsonWebTokenResponseInterface::ACTION_INVALID;
    if ($expired) $action = JsonWebTokenResponseInterface::ACTION_EXPIRED;

    return new JsonWebTokenResponse(
      $action, $valid, $expired
    );
  }

  public function isExpired(string $token): bool {
    $tokPayload = self::payloadFrom($token);
    return !(isset($tokPayload['exp']) && $tokPayload['exp'] > time());
  }

  public static function payloadFrom(string $token): ?array {
    [, $tokPayload,] = explode('.', $token);
    $tokPayload = Base64::decode($tokPayload);

    return json_decode($tokPayload, true);
  }
}
