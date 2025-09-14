<?php

namespace Drupal\drift_eleven\Core\Session;

use Drupal;
use Drupal\drift_eleven\Core\Auth\JsonWebTokenInterface;
use Drupal\drift_eleven\Core\Logger\Logger;
use Drupal\drift_eleven\Core\Logger\LoggerInterface;
use Exception;

class Session implements SessionInterface {
  protected const string TABLE_NAME = D9M7_PROJECT_ID . '_session';
  protected const string TABLE_NAME_SHORT = 'desn';

  protected ?int $id;
  protected int $entityId;
  protected string $token;
  protected string $tokenType;
  protected int $tokenParentId;
  protected string $userAgent;
  protected int $updatedAt;
  protected int $createdAt;
  protected Drupal\Core\Database\Connection $database;

  public function __construct(int $entityId, string $token, string $tokenType, int $tokenParentId, string $userAgent = '', ?int $id = null, int $updatedAt = 0, int $createdAt = 0) {
    $this->id = $id;
    $this->entityId = $entityId;
    $this->token = $token;
    $this->tokenType = $tokenType;
    $this->tokenParentId = $tokenParentId;
    $this->userAgent = $userAgent;
    $this->updatedAt = $updatedAt;
    $this->createdAt = $createdAt;

    $this->database = Drupal::database();
  }

  public function save(): int {
    if ($this->tokenType === JsonWebTokenInterface::TOKEN_ACCESS) {
      if ($this->existsAccess()) {
        return $this->saveAccessExisting();
      } else {
        return $this->saveAccessNew();
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
        'updated_at' => time(),
      ]);

    return $query->execute() ? $this->id : 0;
  }

  protected function existsRefresh(?string $token = null): int {
    $useToken = $this->token;
    if ($token) $useToken = $token;

    $result = $this->database->select(self::TABLE_NAME, self::TABLE_NAME_SHORT)
      ->fields(self::TABLE_NAME_SHORT, ['id'])
      ->condition('token', $useToken)
      ->condition('token_type', JsonWebTokenInterface::TOKEN_REFRESH)
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

    return new SessionUser(
      (int)$result[0]->entity_id,
      (bool)$result[0]->status,
      array_unique($roles),
      array_unique($permissions),
    );
  }
}
