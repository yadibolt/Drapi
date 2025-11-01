<?php

namespace Drupal\drapi\Core\Session\Base;

abstract class SubjectBase {
  protected int $id;
  protected string $username;
  protected bool $active;
  protected array $roles;
  protected array $permissions;
  protected string $langcode;
  protected bool $authenticated;

  public function __construct(int $id, string $username, bool $authenticated, bool $active, array $roles = [], array $permissions = [], string $langcode = 'en') {
    $this->id = $id;
    $this->username = $username;
    $this->active = $active;
    $this->authenticated = $authenticated;
    $this->roles = $roles;
    $this->permissions = array_merge($permissions, ['access content']);
    $this->langcode = $langcode;
  }
  public function toArray(): array {
    return [
      'id' => $this->id,
      'active' => $this->active,
      'authenticated' => $this->authenticated,
      'roles' => $this->roles,
      'permissions' => $this->permissions,
      'langcode' => $this->langcode,
    ];
  }

  public function setId(int $id) : void {
    $this->id = $id;
  }
  public function setUsername(string $username) : void {
    $this->username = $username;
  }
  public function setActive(bool $active) : void {
    $this->active = $active;
  }
  public function setAuthenticated(bool $authenticated) : void {
    $this->authenticated = $authenticated;
  }
  public function setRoles(array $roles) : void {
    $this->roles = $roles;
  }
  public function setPermissions(array $permissions) : void {
    $this->permissions = $permissions;
  }
  public function setLangcode(string $langcode) : void {
    $this->langcode = $langcode;
  }

  public function getId() : int {
    return $this->id;
  }
  public function getUsername() : string {
    return $this->username;
  }
  public function isActive() : bool {
    return $this->active;
  }
  public function isAuthenticated() : bool {
    return $this->authenticated;
  }
  public function getRoles() : array {
    return $this->roles;
  }
  public function getPermissions() : array {
    return $this->permissions;
  }
  public function getLangcode() : string {
    return $this->langcode;
  }
}
