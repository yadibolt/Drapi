<?php

namespace Drupal\pingvin\User;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Exception;
use Random\RandomException;

class User {
  /**
   * Allowed methods for loading user by property.
   */
  protected const array ALLOWED_LOAD_METHODS = ['mail', 'name'];
  /**
   * Password reset token expiry time in seconds.
   */
  protected const int PASSWORD_RESET_TOKEN_EXPIRY = 3600;
  /**
   * Name of the table used for storing password reset tokens.
   *
   * @var string
   */
  protected const string PASSWORD_RESET_TABLE = pw8dr1_PROJECT_ID. '_password_reset';
  /**
   * Shorthand for loading user by email.
   *
   * @var string
   */
  protected const string PASSWORD_RESET_TABLE_SHORTHAND = 'ppr';
  /**
   * Retrieve a user entity by their login, which can be either username or email.
   *
   * @param string $value
   *    The username or email of the user to retrieve.
   * @param string $loadPropertyMethod
   *    The method to use for loading the user. Allowed values are 'mail' or 'name'.
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   * @throws Exception
   *    If an invalid load method is provided.
   * @return Drupal\user\Entity\User|null
   *   The user entity if found, or null if no user matches the provided login.
   */
  public static function retrieveByPropertyMethod(string $value, string $loadPropertyMethod = 'mail'): ?Drupal\user\Entity\User {
    if (!in_array($loadPropertyMethod, self::ALLOWED_LOAD_METHODS)) {
      throw new Exception('Invalid load method. Allowed methods are: ' . implode(', ', self::ALLOWED_LOAD_METHODS));
    }

    $users = Drupal::entityTypeManager()
      ->getStorage('user');

    if ($loadPropertyMethod == 'mail') {
      $users = $users->loadByProperties(['mail' => $value]);
    }
    if ($loadPropertyMethod == 'name') {
      $users = $users->loadByProperties(['name' => $value]);
    }

    if (!$users) return null;

    return reset($users);
  }

  /**
   * Creates a password reset token for the given email.
   *
   * The token is a combination of a hashed email, a request time hash, and a random token.
   * It is structured as: {hashed_email}.{request_time_hash}.{random_token}
   *
   * @param string $mail
   *    The email address for which to create the reset password token.
   * @return string
   *    The generated reset password token.
   * @throws RandomException
   *    If there is an error generating random bytes.
   */
  public static function createResetPasswordToken(string $mail): ?string {
    $config = Drupal::configFactory()->getEditable(pw8dr1_PROJECT_ID . '.settings');
    $salt = $config->get('password_reset_salt') ?: pw8dr1_PROJECT_ID; // TODO: change

    $user = null;
    try {
      $user = self::retrieveByPropertyMethod($mail, 'mail');
    } catch (Exception) {
      return null;
    }

    if (!$user) return null;

    $requestTime = hash_hmac('sha256', (string) time(), $salt);
    $mail = hash_hmac('sha256', $mail, $salt);
    $token = bin2hex(random_bytes(16));
    $langcode = $user->getPreferredLangcode();

    return "$langcode-$mail.$requestTime.$token";
  }

  /**
   * Inserts a password reset token into the database.
   *
   * @param string $mail
   *    The email address associated with the password reset token.
   * @param string $token
   *    The password reset token to insert.
   * @return bool
   *    true if the insertion was successful, false otherwise.
   * @throws Exception
   */
  public static function insertPasswordResetToken(string $mail, string $token): bool {
    $query = Drupal::database()->insert(self::PASSWORD_RESET_TABLE)
      ->fields([
        'mail' => $mail,
        'token' => $token,
        'expires_at' => time() + self::PASSWORD_RESET_TOKEN_EXPIRY,
        'updated_at' => time(),
        'created_at' => time(),
      ]);
    return (bool) $query->execute();
  }

  /**
   * Invalidates all password reset tokens associated with the given email.
   *
   * This method deletes all entries in the password reset table for the specified email.
   *
   * @param string $mail
   *    The email address for which to invalidate password reset tokens.
   * @return bool
   *    true if the deletion was successful, false otherwise.
   */
  public static function invalidatePasswordResetTokens(string $mail): bool {
    $query = Drupal::database()->delete(self::PASSWORD_RESET_TABLE)
      ->condition('mail', $mail);
    return (bool) $query->execute();
  }

  /**
   * Extracts the language code from a reset password token.
   *
   * The token is expected to be in the format: {langcode}-{hashed_email}.{request_time_hash}.{random_token}
   * If the token is malformed or does not contain a valid language code, 'en' is returned as the default.
   *
   * @param string $token
   *    The reset password token from which to extract the language code.
   * @return string
   *    The extracted language code, or 'en' if not found or invalid.
   */
  public static function extractLangcodeFromResetPasswordToken(string $token): string {
    $parts = explode('-', $token);
    if (count($parts) !== 2) return 'en';
    return $parts[0] ?: 'en';
  }

  /**
   * Verifies the validity of a reset password token.
   * This includes checking if the token exists in the database and whether it has expired.
   *
   * @param string $token
   *   The reset password token to verify.
   * @return object|null
   *   The token record object if valid, or null if invalid or expired.
   */

  public static function verifyResetPasswordToken(string $token): ?object {
    $query = Drupal::database()->select(self::PASSWORD_RESET_TABLE, self::PASSWORD_RESET_TABLE_SHORTHAND)
      ->fields(self::PASSWORD_RESET_TABLE_SHORTHAND)
      ->condition('token', $token);
    $resetToken = $query->execute()->fetchObject() ?: null;

    if (!$resetToken) return null;

    // we invalidate all tokens for this mail, no matter
    // if this one is expired or not
    self::invalidatePasswordResetTokens($resetToken->mail);
    // validate expiry
    if ($resetToken->expires_at < time()) return null;

    return $resetToken;
  }
}
