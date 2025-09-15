<?php

namespace Drupal\drift_eleven\Core\Session;

use Drupal;
use Drupal\drift_eleven\Core\Auth\JsonWebTokenInterface;
use Drupal\drift_eleven\Core\Cache\Cache;
use Drupal\drift_eleven\Core\Logger\Logger;
use Drupal\drift_eleven\Core\Logger\LoggerInterface;
use Exception;
use PDO;

class Session implements SessionInterface {
  protected const string TABLE_NAME = D9M7_PROJECT_ID . '_session';
  protected const string TABLE_NAME_SHORT = 'desn';

  protected ?int $id;
  protected int $entityId;
  protected string $token;
  protected string $tokenType;
  protected int $tokenParentId;
  protected string $userAgent;
  protected string $hostname;
  protected int $updatedAt;
  protected int $createdAt;
  protected Drupal\Core\Database\Connection $database;

  public function __construct(int $entityId, string $token, string $tokenType, int $tokenParentId, string $userAgent = '', string $hostname = '', ?int $id = null, int $updatedAt = 0, int $createdAt = 0) {
    $this->id = $id;
    $this->entityId = $entityId;
    $this->token = $token;
    $this->tokenType = $tokenType;
    $this->tokenParentId = $tokenParentId;
    $this->userAgent = $userAgent;
    $this->hostname = $hostname;
    $this->updatedAt = $updatedAt === 0 ? time() : $updatedAt;
    $this->createdAt = $createdAt === 0 ? time() : $createdAt;

    $this->database = Drupal::database();
  }

  public function save(): int {
    if ($this->tokenType === JsonWebTokenInterface::TOKEN_ACCESS) {
      if ($this->existsAccess()) {
        return $this->saveAccessExisting();
      } else {
        $tokenId = $this->saveAccessNew();
        if ($tokenId > 0) {
          // if the access token was created,
          // we also create a cache entry for the entity
          $sessionUser = SessionUser::fromEntityId($this->entityId);
          if ($sessionUser) {
            Cache::make(D9M7_CACHE_KEY . ":session:$this->token", $sessionUser->getCacheStructData());
          }
        }
        return $tokenId;
      }
    } elseif ($this->tokenType === JsonWebTokenInterface::TOKEN_REFRESH) {
      if ($this->existsRefresh()) {
        return $this->saveRefreshExisting();
      } else {
        return $this->saveRefreshNew();
      }
    }

    return false;
  }

  protected function saveAccessNew(): int {
    if (!$this->existsRefresh($this->tokenParentId)) {
      Logger::l('Session save error: Cannot create access token without existing refresh token.', [], LoggerInterface::LEVEL_ERROR);
      return 0;
    }

    $query = $this->database->insert(self::TABLE_NAME)
      ->fields([
        'entity_id' => $this->entityId,
        'token' => $this->token,
        'token_type' => $this->tokenType,
        'token_parent_id' => $this->tokenParentId,
        'user_agent' => $this->userAgent,
        'hostname' => $this->hostname,
        'updated_at' => time(),
        'created_at' => time(),
      ]);

    try {
      $result = $query->execute();
      return (int)$result;
    } catch (Exception $e) {
      Logger::l('Session save error: ' . $e->getMessage(), [], LoggerInterface::LEVEL_ERROR);
      return 0;
    }
  }

  protected function saveAccessExisting(): int {
    $query = $this->database->update(self::TABLE_NAME)
      ->condition('id', $this->id)
      ->fields([
        'entity_id' => $this->entityId,
        'token' => $this->token,
        'token_type' => $this->tokenType,
        'token_parent_id' => $this->tokenParentId,
        'user_agent' => $this->userAgent,
        'hostname' => $this->hostname,
        'updated_at' => time(),
      ]);

    return $query->execute() ? $this->id : 0;
  }

  protected function saveRefreshNew(): int {
    $query = $this->database->insert(self::TABLE_NAME)
      ->fields([
        'entity_id' => $this->entityId,
        'token' => $this->token,
        'token_type' => $this->tokenType,
        'token_parent_id' => 0,
        'user_agent' => $this->userAgent,
        'hostname' => $this->hostname,
        'updated_at' => time(),
        'created_at' => time(),
      ]);

    try {
      $result = $query->execute();
      return (int)$result;
    } catch (Exception $e) {
      Logger::l('Session save error: ' . $e->getMessage(), [], LoggerInterface::LEVEL_ERROR);
      return 0;
    }
  }

  protected function saveRefreshExisting(): int {
    $query = $this->database->update(self::TABLE_NAME)
      ->condition('id', $this->id)
      ->fields([
        'entity_id' => $this->entityId,
        'token' => $this->token,
        'token_type' => $this->tokenType,
        'token_parent_id' => 0,
        'user_agent' => $this->userAgent,
        'hostname' => $this->hostname,
        'updated_at' => time(),
      ]);

    return $query->execute() ? $this->id : 0;
  }

  protected function existsRefresh(?int $id = null): int {
    $result = $this->database->select(self::TABLE_NAME, self::TABLE_NAME_SHORT)
      ->fields(self::TABLE_NAME_SHORT, ['id']);

    // if we have an id, we check by id instead of token
    if ($id !== null) {
      $result = $result->condition('id', $id);
    } else {
      $result = $result->condition('token', $this->token);
    }

    $result = $result->condition('token_type', JsonWebTokenInterface::TOKEN_REFRESH)
      ->execute()
      ->fetchField();

    return $result !== false ? (int)$result : 0;
  }

  protected function existsAccess(): int {
    $result = $this->database->select(self::TABLE_NAME, self::TABLE_NAME_SHORT)
        ->fields(self::TABLE_NAME_SHORT, ['id'])
        ->condition('token', $this->token)
        ->condition('token_type', JsonWebTokenInterface::TOKEN_ACCESS)
        ->condition('token_parent_id', $this->tokenParentId)
        ->execute()
        ->fetchField();

    return $result !== false ? (int)$result : 0;
  }

  public static function find(string $token): bool|object|null {
    $database = Drupal::database();

    return $database->select(self::TABLE_NAME, self::TABLE_NAME_SHORT)
      ->fields(self::TABLE_NAME_SHORT)
      ->condition('token', $token)
      ->execute()
      ->fetchObject();
  }

  public static function findUser(string $token, string $tokenType = JsonWebTokenInterface::TOKEN_ACCESS): ?SessionUser {
    $database = Drupal::database();
    $shortName = self::TABLE_NAME_SHORT;
    $bundleUser = 'user';

    $query = $database->select(self::TABLE_NAME, self::TABLE_NAME_SHORT)
      ->fields(self::TABLE_NAME_SHORT, ['entity_id'])
      ->condition(self::TABLE_NAME_SHORT . '.token', $token)
      ->condition(self::TABLE_NAME_SHORT . '.token_type', $tokenType);

    $query->innerJoin('users_field_data', 'ufd', "$shortName.entity_id = ufd.uid");
    $query->fields('ufd', ['status']);

    $query->innerJoin('user__roles', 'u__r', "u__r.bundle = '$bundleUser' AND $shortName.entity_id = u__r.entity_id");
    $query->fields('u__r', ['roles_target_id']);

    $query->innerJoin('config', 'cf', "CONCAT('user.role.', u__r.roles_target_id) = cf.name");
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

    return new SessionUser(
      (int)$result[0]->entity_id,
      (bool)$result[0]->status,
      array_unique($roles),
      array_unique($permissions),
    );
  }

  public static function delete(string $token, string $tokenType = JsonWebTokenInterface::TOKEN_ACCESS): bool {
    if (empty($token)) return false;

    $result = 0;
    $database = Drupal::database();

    // if we delete an access token, we only delete that token
    // and cache entry if exists
    if ($tokenType === JsonWebTokenInterface::TOKEN_ACCESS) {
      $query = $database->delete(self::TABLE_NAME)
        ->condition('token', $token)
        ->condition('token_type', JsonWebTokenInterface::TOKEN_ACCESS);

      if ($query->execute() > 0) {
        Cache::invalidate(D9M7_CACHE_KEY . ":session:$token");
        return true;
      } else { return false; }
    }

    // if we delete a refresh token, we delete the access tokens too
    // we create temporary access token object to find the refresh token id
    $tokenSession = new self(
      entityId: 0, token: $token, tokenType: JsonWebTokenInterface::TOKEN_ACCESS,
      tokenParentId: 0, userAgent: '', hostname: '', id: null, updatedAt: 0, createdAt: 0
    );
    $refreshTokenId = $tokenSession->existsRefresh();
    if ($refreshTokenId <> 0) {
      // Refresh token
      $query = $database->delete(self::TABLE_NAME)
        ->condition('id', $refreshTokenId)
        ->condition('token', $token)
        ->condition('token_type', JsonWebTokenInterface::TOKEN_REFRESH);
      $result += $query->execute();
      // Access tokens
      $query = $database->delete(self::TABLE_NAME)
        ->condition('token_parent_id', $refreshTokenId)
        ->condition('token_type', JsonWebTokenInterface::TOKEN_ACCESS);
      $result += $query->execute();
    }

    return $result > 0;
  }

  public static function invalidate(string $token, string $tokenType = JsonWebTokenInterface::TOKEN_ACCESS): bool {
    if (empty($token)) return false;
    if (!in_array($tokenType, JsonWebTokenInterface::TOKEN_TYPES)) return false;

    $database = Drupal::database();
    if ($tokenType === JsonWebTokenInterface::TOKEN_ACCESS) {
      $result = $database->select(self::TABLE_NAME, self::TABLE_NAME_SHORT)
        ->fields(self::TABLE_NAME_SHORT, ['id', 'token_parent_id'])
        ->condition('token', $token)
        ->condition('token_type', JsonWebTokenInterface::TOKEN_ACCESS)
        ->execute()
        ->fetchAssoc();

      if (empty($result) || empty($result['token_parent_id'])) return false;

      $result = $database->delete(self::TABLE_NAME)
        ->condition('id', $result['token_parent_id'])
        ->condition('token_type', JsonWebTokenInterface::TOKEN_REFRESH);
      $affected = $result->execute();

      $result = $database->delete(self::TABLE_NAME)
        ->condition('token', $token)
        ->condition('token_type', JsonWebTokenInterface::TOKEN_ACCESS);
      $affected += $result->execute();

      return $affected > 0;
    }
    if ($tokenType === JsonWebTokenInterface::TOKEN_REFRESH) {
      $result = $database->select(self::TABLE_NAME, self::TABLE_NAME_SHORT)
        ->fields(self::TABLE_NAME_SHORT, ['id'])
        ->condition('token', $token)
        ->condition('token_type', JsonWebTokenInterface::TOKEN_REFRESH)
        ->execute()
        ->fetchAssoc();

      if (empty($result) || empty($result['id'])) return false;

      $result = $database->select(self::TABLE_NAME, self::TABLE_NAME_SHORT)
        ->fields(self::TABLE_NAME_SHORT, ['token'])
        ->condition('token_parent_id', $result['id'])
        ->condition('token_type', JsonWebTokenInterface::TOKEN_ACCESS)
        ->execute()->fetchAllAssoc('token', PDO::FETCH_ASSOC);

      if (empty($result)) return false;

      $tokens = [];
      foreach ($result as $tok => $_) {
        $tokens[] = $tok;
        Cache::invalidate(D9M7_CACHE_KEY . ":session:$tok");
      }

      $result = $database->delete(self::TABLE_NAME)
        ->condition('token', $tokens, 'IN')
        ->condition('token_type', JsonWebTokenInterface::TOKEN_ACCESS);
      $affected = $result->execute();

      return $affected > 0;
    }

    return false;
  }
}
