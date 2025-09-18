<?php

namespace Drupal\drift_eleven\Core\Session;

use Drupal;
use Exception;
use Random\RandomException;

class SessionUser implements SessionUserInterface {
  protected int $entityId;
  protected array $roles;
  protected array $permissions;
  protected bool $active = false;
  protected string $langcode = 'en';

  public function __construct(int $entityId, bool $active, array $roles, array $permissions, string $langcode) {
    $this->entityId = $entityId;
    $this->active = $active;
    $this->roles = $roles;
    $this->permissions = $permissions;
    $this->langcode = $langcode;
  }

  public function getEntityId(): int {
    return $this->entityId;
  }

  public function isActive(): bool {
    return $this->active;
  }

  public function getRoles(): array {
    return $this->roles ?: [];
  }

  public function getLangcode(): string {
    return $this->langcode;
  }

  public function getPermissions(): array {
    return $this->permissions ?: [];
  }

  public function getCacheStructData(): array {
    return [
      'entityId' => $this->entityId,
      'active' => $this->active,
      'roles' => $this->roles,
      'permissions' => $this->permissions,
      'langcode' => $this->langcode,
    ];
  }

  public static function fromEntityId(int $entityId): ?SessionUserInterface {
    $database = Drupal::database();
    $bundleUser = 'user';

    $query = $database->select('users_field_data', 'ufd')
      ->fields('ufd', ['uid', 'status', 'langcode'])
      ->condition('ufd.uid', $entityId);

    $query->innerJoin('user__roles', 'u__r', "u__r.bundle = '$bundleUser' AND ufd.uid = u__r.entity_id");
    $query->fields('u__r', ['roles_target_id']);

    $query->innerJoin('config', 'cf', "u__r.roles_target_id = cf.name");
    $query->fields('cf', ['data']);

    $result = $query->execute()->fetchAll();

    if (empty($result)) return null;

    $roles = [];
    $permissions = [];
    foreach ($result as $row) {
      $roles[] = $row->roles_target_id;

      $roleData = unserialize($row->data);
      if (isset($roleData['permissions']) && is_array($roleData['permissions'])) {
        $permissions = array_merge($permissions, $roleData['permissions']);
      }
    }

    return new self(
      $entityId,
      (bool)$result[0]->status,
      array_unique($roles),
      array_unique($permissions),
      $langcode = $result[0]->langcode ?: 'en'
    );
  }

  /**
   * @throws RandomException
   */
  public static function makeResetToken(string $mail, string $langcode): string {
    $config = Drupal::configFactory()->getEditable(D9M7_CONFIG_KEY);
    $salt = $config->get('jwtSecret') ?: D9M7_PROJECT_ID;

    $requestTime = hash_hmac('sha256', (string) time(), $salt);
    $mail = hash_hmac('sha256', $mail, $salt);
    $token = bin2hex(random_bytes(16));

    return "$langcode-$mail.$requestTime.$token";
  }

  public static function fromResetToken(string $token): ?string {
    $query = Drupal::database()->select(self::FORGOT_PASSWORD_TABLE_NAME, self::FORGOT_PASSWORD_TABLE_NAME_SHORT)
      ->fields(self::FORGOT_PASSWORD_TABLE_NAME_SHORT)
      ->condition('token', $token);
    $result = $query->execute()->fetchObject() ?: null;

    if (!$result) return null;
    if ($result->expires_at < time()) return null;
    self::deleteResetTokens($result->mail);

    return $result->mail;
  }

  public static function insertResetToken(string $mail, string $token): bool {
    self::deleteResetTokens($mail);

    $query = Drupal::database()->insert(self::FORGOT_PASSWORD_TABLE_NAME)
      ->fields([
        'token' => $token,
        'mail' => $mail,
        'expires_at' => time() + self::PASSWORD_RESET_TOKEN_EXPIRY,
        'updated_at' => time(),
        'created_at' => time(),
      ]);

    try {
      return $query->execute() > 0;
    } catch (Exception) {
      return false;
    }
  }

  public static function deleteResetTokens(string $mail): void {
    Drupal::database()->delete(self::FORGOT_PASSWORD_TABLE_NAME)
      ->condition('mail', $mail)
      ->execute();
  }
}
