<?php

namespace Drupal\drapi\Core\Session;

use Drupal;
use Drupal\drapi\Core\Auth\Enum\JWTIntent;
use Drupal\drapi\Core\Auth\JWT;
use Drupal\drapi\Core\Session\Base\SubjectBase;
use Exception;

class Subject extends SubjectBase {
  public static function make(int $id, bool $active, bool $authenticated, array $roles = [], array $permissions = [], string $langcode = 'en'): self {
    return new self($id, $active, $authenticated, $roles, $permissions, $langcode);
  }
  public static function makeAnonymous(): self {
    return new self(0, false, true, ['anonymous'], ['access content'], 'en');
  }
  public static function getByMail(string $mail): int {
    $query = Drupal::database()->select('users_field_data', 'a')
      ->fields('a', ['uid', 'langcode'])
      ->condition('a.mail', $mail);

    return (int)$query->execute()->fetchField();
  }
  public static function generateForgotPasswordToken(string $mail, string $langcode): ?string {
    $userId = Subject::getByMail($mail);
    if ($userId <= 0) return null;

    return JWT::make(JWTIntent::RESET_PASSWORD, [
      'user_id' => $userId,
      'mail' => $mail,
      'langcode' => $langcode,
    ]);
  }
  public static function forgotPasswordRecordExists(string $token): bool {
    $query = Drupal::database()->select(SUBJECT_RESET_PASSWORD_TABLE_NAME_DEFAULT, 'a')
      ->fields('a')
      ->condition('token', $token);
    $record = $query->execute()->fetchAll();

    if (empty($record)) return false;

    $record = reset($record);
    if ($record->expires_at > time()) return false;

    return true;
  }
  public static function insertForgotPasswordToken(string $token): bool {
    $payload = JWT::payloadFrom($token);

    if (empty($payload) || !isset($payload['data']) || !isset($payload['data']['user_id']) || !isset($payload['data']['mail']) || !isset($payload['data']['langcode'])) {
      return false;
    }

    $userId = (int)$payload['data']['user_id'];
    $mail = $payload['data']['mail'];
    $langcode = $payload['data']['langcode'];
    $expiresAt = $payload['exp'] ?? JWT_RESET_PASSWORD_TTL_DEFAULT;

    if (!self::deleteForgotPasswordRecords($mail)) return false;
    $query = Drupal::database()->insert(SUBJECT_RESET_PASSWORD_TABLE_NAME_DEFAULT)
      ->fields([
        'entity_id' => $userId,
        'mail' => $mail,
        'token' => $token,
        'langcode' => $langcode,
        'expires_at' => $expiresAt,
        'created_at' => time(),
        'updated_at' => time()
      ]);
    try {
      if (!$query->execute()) return false;
    } catch (Exception) {
      return false;
    }

    return true;
  }
  public static function deleteForgotPasswordRecords(string $mail): bool {
    Drupal::database()->delete(SUBJECT_RESET_PASSWORD_TABLE_NAME_DEFAULT)
      ->condition('mail', $mail)
      ->execute();

    return true;
  }
}
