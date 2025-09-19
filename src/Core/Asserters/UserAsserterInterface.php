<?php

namespace Drupal\drift_eleven\Core\Asserters;

interface UserAsserterInterface {
  public function assertUsernameRequirements(string $username): bool;

  public function assertPasswordRequirements(string $password): bool;

  public function assertPasswordMatch(string $password, string $confirmPassword): bool;
}
