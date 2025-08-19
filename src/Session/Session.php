<?php

namespace Drupal\pingvin\Session;

use Drupal;
use Drupal\pingvin\Auth\JsonWebToken;
use Exception;

class Session {
  /**
   * The session table name.
   *
   * @var string
   */
  protected const string SESSION_TABLE = pw8dr1_PROJECT_ID . '_session';
  /**
   * The session table shorthand.
   *
   * This is used for database queries to refer to the session table.
   *
   * @var string
   */
  protected const string SESSION_TABLE_SHORTHAND = 'ps';

  /**
   * Method retrieves a session/s by various parameters.
   * This method can be used to retrieve a session by token or user ID.
   *
   * @param string $token
   *    The session token to retrieve.
   * @param string $tokenType
   *    The type of the token, either 'access' or 'refresh'.
   * @param string|int $userId
   *    The user ID associated with the session. This can be a string or an integer.
   * @param int $tiedToId
   *    The ID of the session that this session is tied to. Default is 0, which means no tie.
   * @return object|array
   *    Returns an object with session data if a single session is found,
   *    or an array of objects if multiple sessions are found.
   * @throws Exception
   */
  public static function retrieve(string $token = '', string $tokenType = '', string|int $userId = '', int $refreshTokenId = 0): object|array {
    // this method tries to find appropriate session for the given parameters
    // and returns an array|object with session data if found, or an empty array if not found.
    if (!empty($token) && !empty($tokenType) && in_array($tokenType, [JsonWebToken::JWT_TOKEN_TYPE_ACCESS, JsonWebToken::JWT_TOKEN_TYPE_REFRESH])) {
      // case when we retrieve by session token
      $query = Drupal::database()->select(self::SESSION_TABLE, self::SESSION_TABLE_SHORTHAND)
        ->fields(self::SESSION_TABLE_SHORTHAND)
        ->condition('token', $token)
        ->condition('token_type', $tokenType);

      if ($refreshTokenId !== 0) {
        $query->condition('refresh_token_id', $refreshTokenId);
      }

      $query->range(0, 1);

      return $query->execute()->fetchObject() ?: [];
    }

    if (!empty($userId) && !empty($tokenType) && in_array($tokenType, [JsonWebToken::JWT_TOKEN_TYPE_ACCESS, JsonWebToken::JWT_TOKEN_TYPE_REFRESH])) {
      // case when we retrieve by user ID
      $query = Drupal::database()->select(self::SESSION_TABLE, self::SESSION_TABLE_SHORTHAND)
        ->fields(self::SESSION_TABLE_SHORTHAND)
        ->condition('entity_id', $userId)
        ->condition('token_type', $tokenType)
        ->orderBy('created_at', 'DESC');

      if ($refreshTokenId !== 0) {
        $query->condition('refresh_token_id', $refreshTokenId);
      }

      return $query->execute()->fetchAll() ?: [];
    }

    throw new Exception('Using ::retrieve() method without parameters is not allowed. Please provide a combination of: token, tokenType or userId and tokenType.');
  }

  public static function delete(string $token): bool {
    // This method deletes a session by its token.
    // It returns true if the session was deleted, false otherwise.
    $query = Drupal::database()->delete(self::SESSION_TABLE)
      ->condition('token', $token)
      ->execute();

    return $query > 0;
  }

  public static function invalidateAccessTokens(string $refreshToken): bool {
    // invalidates all access tokens tied to the given refresh token
    $query = Drupal::database()->delete(self::SESSION_TABLE)
      ->condition('token', $refreshToken)
      ->condition('token_type', JsonWebToken::JWT_TOKEN_TYPE_REFRESH)
      ->execute();

    return $query > 0;
  }
}
