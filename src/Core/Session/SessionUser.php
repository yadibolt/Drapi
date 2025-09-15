<?php

namespace Drupal\drift_eleven\Core\Session;

use Drupal;

class SessionUser implements SessionUserInterface {
  protected int $entityId;
  protected array $roles;
  protected array $permissions;
  protected bool $active = false;

  public function __construct(int $entityId, bool $active, array $roles, array $permissions) {
    $this->entityId = $entityId;
    $this->active = $active;
    $this->roles = $roles;
    $this->permissions = $permissions;
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

  public function getPermissions(): array {
    return $this->permissions ?: [];
  }

  public function getCacheStructData(): array {
    return [
      'entityId' => $this->entityId,
      'active' => $this->active,
      'roles' => $this->roles,
      'permissions' => $this->permissions,
    ];
  }

  public static function fromEntityId(int $entityId): ?SessionUserInterface {
    $database = Drupal::database();
    $bundleUser = 'user';

    $query = $database->select('users_field_data', 'ufd')
      ->fields('ufd', ['uid', 'status'])
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
    );
  }
}
