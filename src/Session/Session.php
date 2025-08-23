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
   * The unique identifier for the record.
   * This parameter varies depending on the implementation. <p>
   * !!! This is used as a pivot for updating existing records. !!!
   *
   * @var int|string|null $id
   */
  protected null|int|string $id;
  /**
   * The ID of the entity (user) associated with the session.
   *
   * @var int|string $entityId
   */
  protected int|string $entityId;
  /**
   * The access token of an entity.
   *
   * @var string $accessToken
   */
  protected string $accessToken;
  /**
   * The refresh token of an entity.
   *
   * @var string $refreshToken
   */
  protected string $refreshToken;
  /**
   * The refresh token of an entity.
   *
   * @var string $userAgent
   */
  protected string $userAgent;
  /**
   * Timestamp when the session was updated.
   *
   * @var int $updatedAt
   */
  protected int $updatedAt;
  /**
   * Timestamp when the session was created.
   *
   * @var int $createdAt
   */
  protected int $createdAt;

  /**
   * Constructs a new Session for the user.
   * Session created by this constructor is not definite.
   * Using save() method is required to store it in the database.
   *
   * @param int|string $entityId
   *    The ID of the entity (user) associated with the session.
   * @param string $accessToken
   *    The access token of an entity.
   * @param string $refreshToken
   *   The refresh token of an entity.
   * @param string $userAgent
   *   The user agent string from the user's device request.
   * @param int $updatedAt
   *   Timestamp when the session was updated.
   * @param int $createdAt
   *   Timestamp when the session was created.
   */
  public function __construct(int|string $entityId, string $accessToken, string $refreshToken, string $userAgent, int $updatedAt = 0, int $createdAt = 0) {
    $this->id = null;
    $this->entityId = $entityId;
    $this->accessToken = $accessToken;
    $this->refreshToken = $refreshToken;
    $this->userAgent = $userAgent;
    $this->createdAt = ($createdAt !== 0) ? $createdAt : time();
    $this->updatedAt = ($updatedAt !== 0) ? $updatedAt : time();
  }

  /**
   * Method saves the session to the database.
   * In case of a new session, a new record is inserted. (one for access token, one for refresh token)
   * If the access token already exists, it will be returned instead in a form of self.
   * If the refresh token already exists, it will be updated with the new access token and returned in a form of self.
   *
   * @return Session|null
   *    Returns the saved Session object with updated ID if it was a new session,
   *    or null if the save operation failed.
   * @throws Exception
   *   If there is an error during the database operation.
   */
  public function save(): ?self {
    // we first need to check if the access token already exists
    // with the combination of accessToken and refreshToken
    $query = Drupal::database()->select(self::SESSION_TABLE, self::SESSION_TABLE_SHORTHAND)
      ->fields(self::SESSION_TABLE_SHORTHAND)
      ->condition('token', $this->refreshToken)
      ->condition('token_type', JsonWebToken::JWT_TOKEN_TYPE_REFRESH);
    $existingRefreshToken = $query->execute()->fetchObject() ?: null;

    if ($existingRefreshToken !== null) {
      // so the refresh token already exists, we need to update the access token
      $query = Drupal::database()->select(self::SESSION_TABLE, self::SESSION_TABLE_SHORTHAND)
        ->condition('refresh_token_id', $existingRefreshToken->id);
      $existingAccessToken = $query->execute()->fetchObject() ?: null;

      // we set the record ID to the existing access token ID
      // so we can update it later in the save process
      // we strictly do not update the refresh tokens as they
      // should remain the same for the user session and should be only deleted
      // or created. Anyway, this is a rare case that can happen due to
      // bad implementations.
      if ($existingAccessToken !== null) $this->id = $existingAccessToken->id;
    }

    $queryBase = Drupal::database();

    if ($this->id !== null) {
      // we update the access token tied to the existing refresh token
      $this->updatedAt = time();

      $query = $queryBase->update(self::SESSION_TABLE)
        ->condition('id', $this->id)
        ->fields([
          'token' => $this->accessToken,
          'updated_at' => $this->updatedAt,
        ]);

      return $query->execute() ? $this : null;
    } else {
      // we create a fresh, new record for access token and refresh token
      $query = $queryBase->insert(self::SESSION_TABLE)
        ->fields([
          'entity_id' => $this->entityId,
          'refresh_token_id' => 0,
          'token' => $this->refreshToken,
          'token_type' => JsonWebToken::JWT_TOKEN_TYPE_REFRESH,
          'user_agent' => $this->userAgent,
          'created_at' => $this->createdAt,
          'updated_at' => $this->updatedAt,
        ]);
      $refreshTokenId = $query->execute();

      if ($refreshTokenId === 0) return null;

      $query = $queryBase->insert(self::SESSION_TABLE)
        ->fields([
          'entity_id' => $this->entityId,
          'refresh_token_id' => $refreshTokenId,
          'token' => $this->accessToken,
          'token_type' => JsonWebToken::JWT_TOKEN_TYPE_ACCESS,
          'user_agent' => $this->userAgent,
          'created_at' => $this->createdAt,
          'updated_at' => $this->updatedAt,
        ]);

      $accessTokenId = $query->execute();

      if ($accessTokenId === 0) return null;
    }

    // reset the pivot ID
    $this->id = null;

    return $this;
  }

  /**
   * Method retrieves a session/s by various parameters.
   * This method can be used to retrieve a session by token or user ID.
   * This method does not instantiate a Session object, it returns raw data from the database.
   *
   * @param string $token
   *    The session token to retrieve.
   * @param string $tokenType
   *    The type of the token, either 'access' or 'refresh'.
   * @param string|int $userId
   *    The user ID associated with the session. This can be a string or an integer.
   * @param int $refreshTokenId
   *    The ID of the refresh token associated with the session. Default is 0 (not used).
   * @return object|array
   *    Returns an object with session data if a single session is found,
   *    or an array of objects if multiple sessions are found.
   * @throws Exception
   */
  public static function retrieve(string $token = '', string $tokenType = '', string|int $userId = '', int $refreshTokenId = 0): null|self|array {
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

      $result = $query->execute()->fetchObject() ?: [];
      if (empty($result)) return null;

      return new self(
        entityId: $result->entity_id,
        accessToken: $result->token_type === JsonWebToken::JWT_TOKEN_TYPE_ACCESS ? $result->token : 0,
        refreshToken: $result->token_type === JsonWebToken::JWT_TOKEN_TYPE_REFRESH ? $result->token : 0,
        userAgent: $result->user_agent,
        updatedAt: $result->updated_at,
        createdAt: $result->created_at
      );
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

      $result = $query->execute()->fetchAll() ?: [];
      if (empty($result)) return null;

      $tokens = [];
      foreach ($result as $row) {
        $tokens[] = new self(
          entityId: $row->entity_id,
          accessToken: $row->token_type === JsonWebToken::JWT_TOKEN_TYPE_ACCESS ? $row->token : 0,
          refreshToken: $row->token_type === JsonWebToken::JWT_TOKEN_TYPE_REFRESH ? $row->token : 0,
          userAgent: $row->user_agent,
          updatedAt: $row->updated_at,
          createdAt: $row->created_at
        );
      }

      return $tokens;
    }

    throw new Exception('Using ::retrieve() method without parameters is not allowed. Please provide a combination of: token, tokenType or userId and tokenType.');
  }

  /**
   * Deletes a session by its token.
   * If the token is a refresh token, all associated access tokens will also be deleted.
   *
   * @param string $token
   *    The token of the session to delete.
   * @return bool
   *    Returns true if any sessions were deleted, false otherwise.
   */
  public static function delete(string $token): bool {
    $query = Drupal::database()->select(self::SESSION_TABLE, self::SESSION_TABLE_SHORTHAND)
      ->fields(self::SESSION_TABLE_SHORTHAND)
      ->condition('token', $token)
      ->condition('token_type', JsonWebToken::JWT_TOKEN_TYPE_REFRESH);
    $refreshToken = $query->execute()->fetchObject() ?: null;

    if ($refreshToken === null) {
      $query = Drupal::database()->delete(self::SESSION_TABLE)
        ->condition('token', $token);
    } else {
      // we need to remove all access tokens tied to this refresh token
      // and the token itself
      $query = Drupal::database()->delete(self::SESSION_TABLE);

      $or = Drupal::database()->condition('OR');
      $or->condition('refresh_token_id', $refreshToken->id);
      $or->condition('token', $token);

      $query->condition($or);
    }

    return $query->execute() > 0;
  }

  /**
   * Invalidates all access tokens tied to the given refresh token.
   *
   * @param string $refreshToken
   *     The refresh token whose associated access tokens are to be invalidated.
   * @return bool
   *     Returns true if any access tokens were invalidated, false otherwise.
   */
  public static function invalidateAccessTokens(string $refreshToken): bool {
    $query = Drupal::database()->select(self::SESSION_TABLE, self::SESSION_TABLE_SHORTHAND)
      ->fields(self::SESSION_TABLE_SHORTHAND)
      ->condition('token', $refreshToken)
      ->condition('token_type', JsonWebToken::JWT_TOKEN_TYPE_REFRESH);
    $refreshToken = $query->execute()->fetchObject() ?: null;

    if (empty($refreshToken)) return false;

    // invalidates all access tokens tied to the given refresh token
    $query = Drupal::database()->delete(self::SESSION_TABLE)
      ->condition('refresh_token_id', $refreshToken->id)
      ->condition('token_type', JsonWebToken::JWT_TOKEN_TYPE_ACCESS)
      ->execute();

    return $query > 0;
  }

  public static function invalidateRefreshTokens(string $accessToken): bool {
    $query = Drupal::database()->select(self::SESSION_TABLE, self::SESSION_TABLE_SHORTHAND)
      ->fields(self::SESSION_TABLE_SHORTHAND)
      ->condition('token', $accessToken)
      ->condition('token_type', JsonWebToken::JWT_TOKEN_TYPE_ACCESS);
    $accessToken = $query->execute()->fetchObject() ?: null;

    if (empty($accessToken)) return false;

    $query = Drupal::database()->delete(self::SESSION_TABLE)
      ->condition('id', $accessToken->refresh_token_id)
      ->condition('token_type', JsonWebToken::JWT_TOKEN_TYPE_REFRESH)
      ->execute();

    return $query > 0;
  }

  /**
   * Gets the token associated with the session.
   *
   * @return string
   *    Returns the token as a string.
   */
  public function getToken(): string {
    return $this->accessToken;
  }

  /**
   * Gets the entity ID associated with the token.
   *
   * @return int|string
   *    Returns the entity ID as an integer or string.
   */
  public function getEntityId(): int|string {
    return $this->entityId;
  }
}
