<?php

namespace Drupal\drift_eleven\Core\Session;

interface SessionUserInterface {
  public const string FORGOT_PASSWORD_TABLE_NAME = D9M7_PROJECT_ID . '_password_reset';
  public const string FORGOT_PASSWORD_TABLE_NAME_SHORT = 'depr';
  public const int PASSWORD_RESET_TOKEN_EXPIRY = 3600; // TODO: make this configurable
  /**
   * Constructs a new SessionUser object.
   *
   * @param int $entityId the user entity ID
   * @param bool $active whether the user is active
   * @param array $roles the user roles
   * @param array $permissions the user permissions
   */
  public function __construct(int $entityId, bool $active, array $roles, array $permissions, string $langcode);

  public function isActive(): bool;

  public function getRoles(): array;

  public function getPermissions(): array;
  /**
   * Returns an array representation of the user suitable for caching.
   *
   * @return array an associative array containing the user's entityId, active status, roles, and permissions.
   */
  public function getCacheStructData(): array;
  /**
   * Creates a SessionUser instance from a given user entity ID.
   *
   * @param int $entityId the user entity ID
   * @return SessionUserInterface|null the SessionUser instance or null if the user does not exist
   */
  public static function fromEntityId(int $entityId): ?SessionUserInterface;

  /**
   * Generates a reset token for various user operations (e.g., password reset).
   *
   * @param string $mail the user's email address
   * @return string the generated reset token
   */
  public static function makeResetToken(string $mail, string $langcode): string;
}
