<?php

namespace Drupal\drift_eleven\Core\Auth;

use Drupal;
use Drupal\drift_eleven\Core\Auth\Enum\JWTResponseIntent;
use Drupal\drift_eleven\Core\Utility\Base64;
use Drupal\drift_eleven\Core\Auth\Enum\JWTIntent;
use Drupal\drift_eleven\Core\Utility\Logger;
use Drupal\drift_eleven\Core\Utility\Enum\LoggerIntent;

class JWT {
  protected const string HASH_ALGO = 'sha512';

  protected int $accessTokenTTL;
  protected int $accessTokenUnlimitedTTL;
  protected int $refreshTokenTTL;
  protected int $resetPasswordTTL;
  protected string $tokenSecret;
  protected string $token;

  public function __construct(string $token = '') {
    $configuration = Drupal::configFactory()->get(JWT_CONFIG_NAME_DEFAULT);
    $this->tokenSecret = $configuration->get('token_secret') ?? JWT_SECRET_DEFAULT;
    $this->accessTokenTTL = $configuration->get('access_token_ttl') ?? JWT_ACCESS_TOKEN_TTL_DEFAULT;
    $this->accessTokenUnlimitedTTL = $configuration->get('access_token_unlimited_ttl') ?? JWT_ACCESS_TOKEN_UNLIMITED_TTL_DEFAULT;
    $this->refreshTokenTTL = $configuration->get('refresh_token_ttl') ?? JWT_REFRESH_TOKEN_TTL_DEFAULT;
    $this->resetPasswordTTL = $configuration->get('reset_password_ttl') ?? JWT_RESET_PASSWORD_TTL_DEFAULT;
    $this->token = $token;

    if ($this->tokenSecret === JWT_SECRET_DEFAULT) {
      Logger::l(
        level: LoggerIntent::WARNING,
        message: 'The JWT secret is set to default. You should immediately change the secret key in the configuration!',
      );
    }
  }

  public static function make(JWTIntent $tokenType, array|string $data = [], int $tokenTTL = 0): string {
    $jwt = new self();
    $ttl = match($tokenType) {
      JWTIntent::ACCESS_TOKEN => $jwt->accessTokenTTL,
      JWTIntent::ACCESS_TOKEN_UNLIMITED => $jwt->accessTokenUnlimitedTTL,
      JWTIntent::REFRESH_TOKEN => $jwt->refreshTokenTTL,
      JWTIntent::RESET_PASSWORD => $jwt->resetPasswordTTL,
    };

    if (!empty($tokenTTL)) $ttl = $tokenTTL;

    $payload = [
      'mod_sig' => JWT_SIGNATURE_HASH_DEFAULT,
      'iss' => JWT_ISSUER_DEFAULT,
      'iat' => time(),
      'exp' => time() + $ttl - 1000,
    ];

    if (!empty($data)) $payload['data'] = $data;

    $header = Base64::encode(json_encode(["alg" => "HS512", "typ" => "JWT"]));
    $payload = Base64::encode(json_encode($payload));
    $signature = Base64::encode(hash_hmac(self::HASH_ALGO, $header . '.' . $payload, $jwt->tokenSecret, true));

    return $header . '.' . $payload . '.' . $signature;
  }

  public static function check(string $token): JWTResponse {
    if (empty($token)) return new JWTResponse(
      JWTResponseIntent::INVALID_FORMAT
    );

    $jwt = new self();
    [$header, $payload, $signature] = explode('.', $token);
    $signature = Base64::decode($signature);
    $expectedSignature = hash_hmac(self::HASH_ALGO, $header . '.' . $payload, $jwt->tokenSecret, true);

    $isValid = hash_equals($signature, $expectedSignature);
    $isExpired = $jwt->checkTTL($payload);

    if (!$isValid) return new JWTResponse(
      JWTResponseIntent::INVALID
    );
    if ($isExpired) return new JWTResponse(
      JWTResponseIntent::EXPIRED
    );

    return new JWTResponse(
      JWTResponseIntent::OK
    );
  }

  public static function payloadFrom(string $token): ?array {
    if (empty($token)) return null;

    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    $payload = Base64::decode($parts[1]);
    return json_decode($payload, true);
  }

  protected function checkTTL(string $payloadPart): bool {
    $payload = json_decode(Base64::decode($payloadPart), true);
    if (empty($payload) || !isset($payload['exp'])) return true;
    return !($payload['exp'] > time());
  }
}
