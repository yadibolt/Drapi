<?php

namespace Drupal\drift_eleven\Core\Session;

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

  public function isActive(): bool {
    return in_array('active', $this->roles, true);
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
}
