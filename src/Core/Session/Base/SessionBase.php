<?php

namespace Drupal\drapi\Core\Session\Base;

use Drupal;
use Drupal\Core\Database\Connection;
use Drupal\drapi\Core\Auth\Enum\JWTIntent;
use Drupal\drapi\Core\Auth\JWT;
use Drupal\drapi\Core\Session\Enum\SubjectIntent;
use Drupal\drapi\Core\Session\Subject;
use Exception;

abstract class SessionBase {
  protected int $id;
  protected int $entityId;
  protected string $token;
  protected string $userAgent;
  protected string $ip;
  protected int $updatedAt = 0;
  protected int $createdAt = 0;
  protected ?Subject $subject = null;
  protected Connection $conn;

  public function __construct(string $token, string $userAgent = '', string $ip = '') {
    $this->conn = Drupal::database();
    $this->token = $token;
    $this->userAgent = $userAgent;
    $this->ip = $ip;
  }
  public function find(bool $invokeWithUser = true): ?self {
    $query = $this->conn->select(SESSION_TABLE_NAME_DEFAULT, 'a')
      ->fields('a')
      ->condition('access_token', $this->token);

    $query->innerJoin('users_field_data', 'ufd', 'a.entity_id = ufd.uid');
    $query->fields('ufd', ['langcode', 'status', 'name']);

    $record = $query->execute()->fetchAll();

    if (empty($record)) return null;

    $record = reset($record);
    if (JWT::check($record->access_token)->isExpired()) return null;
    if (JWT::check($record->refresh_token)->isExpired()) return null;

    $this->id = $record->id;
    $this->entityId = (int)$record->entity_id;
    $this->token = $record->access_token;
    $this->userAgent = $record->user_agent;
    $this->ip = $record->ip;
    $this->updatedAt = (int)$record->updated_at;
    $this->createdAt = (int)$record->created_at;

    if (!$invokeWithUser) return $this;

    $rolesQuery = $this->conn->select('user__roles', 'a')
      ->fields('a')
      ->condition('a.entity_id', $this->entityId);

    $rolesQuery->innerJoin('config', 'c', "CONCAT('user.role.', a.roles_target_id) = c.name");
    $rolesQuery->fields('c', ['data']);

    $extra = $rolesQuery->execute()->fetchAll();

    $roles = []; $permissions = [];
    foreach ($extra as $row) {
      $roles[] = $row->roles_target_id;

      $roleData = unserialize($row->data);
      if (isset($roleData['permissions']) && is_array($roleData['permissions'])) {
        $permissions = array_merge($permissions, $roleData['permissions']);
      }
    }

    $this->subject = Subject::make(
      id: $record->entity_id,
      username: $record->name,
      active: (bool)$record->status,
      authenticated: true,
      roles: $roles,
      permissions: $permissions,
      langcode: $record->langcode,
    );

    return $this;
  }
  public function create(): ?self {
    $payload = JWT::payloadFrom($this->token);

    if (!$this->checkPayload($payload)) return null;
    if (!empty($this->tokenExists())) return null;

    $refreshToken = JWT::make(JWTIntent::REFRESH_TOKEN, [
      'user_id' => $payload['data']['user_id'],
    ]);

    $query = $this->conn->insert(SESSION_TABLE_NAME_DEFAULT)
      ->fields([
        'entity_id' => (int)$payload['data']['user_id'],
        'access_token' => $this->token,
        'refresh_token' => $refreshToken,
        'user_agent' => $this->userAgent,
        'ip' => $this->ip,
        'expires_at' => time() + SESSION_RECORD_TTL_DEFAULT,
        'created_at' => time(),
        'updated_at' => time(),
      ]);

    try {
      if (!$query->execute()) return null;
    } catch (Exception $e) {
      return null;
    }

    $this->entityId = (int)$payload['data']['user_id'];
    $this->createdAt = time();
    $this->updatedAt = time();

    return $this;
  }
  public function update(): ?self {
    $record = $this->tokenExists();
    if (empty($record)) return null;

    $payload = JWT::payloadFrom($this->token);
    if (!$this->checkPayload($payload)) return null;

    if (JWT::check($record['refresh_token'])->isExpired()) return null;

    $newToken = JWT::make(JWTIntent::ACCESS_TOKEN, [
      'user_id' => $payload['data']['user_id'],
      'username' => $payload['data']['username'],
      'type' => SubjectIntent::AUTHENTICATED,
      'langcode' => $payload['data']['langcode'],
    ]);

    $query = $this->conn->update(SESSION_TABLE_NAME_DEFAULT)
      ->fields([
        'access_token' => $newToken,
        'expires_at' => time() + SESSION_RECORD_TTL_DEFAULT,
        'updated_at' => time(),
      ])
      ->condition('id', $record['id']);

    try {
      if (!$query->execute()) return null;
    } catch (Exception $e) {
      return null;
    }

    $this->token = $newToken;
    $this->updatedAt = time();

    return $this;
  }
  public function delete(): void {
    $this->conn->delete(SESSION_TABLE_NAME_DEFAULT)
      ->condition('access_token', $this->token)
      ->execute();
  }
  public static function deleteInactiveSessions(): void {
    $conn = Drupal::database();
    $conn->delete(SESSION_TABLE_NAME_DEFAULT)
      ->condition('expires_at', time(), '<')
      ->execute();
  }
  protected function tokenExists(): ?array {
    $query = $this->conn->select(SESSION_TABLE_NAME_DEFAULT, 'a')
      ->fields('a', ['id', 'refresh_token']);

    $orGroup = $query->orConditionGroup()
      ->condition('access_token', $this->token);
    $query->condition($orGroup);

    $record = $query->execute()->fetchAll();
    if (empty($record)) return null;

    $record = reset($record);
    return [
      'id' => (int)$record->id,
      'refresh_token' => $record->refresh_token,
    ];
  }
  protected function checkPayload(array $payload): bool {
    if (empty($payload)) return false;
    if (!isset($payload['data']))

      if (!isset($payload['data']['user_id'])) return false;
    if (!is_numeric($payload['data']['user_id'])) return false;
    if ((int)$payload['data']['user_id'] <= 0) return false;

    if (!isset($payload['data']['type'])) return false;
    if (!is_string($payload['data']['type'])) return false;
    if ($payload['data']['type'] !== SubjectIntent::AUTHENTICATED->value) return false;

    return true;
  }

  public function getSubject(): Subject {
    return $this->subject;
  }
}
