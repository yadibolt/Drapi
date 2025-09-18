<?php

namespace Drupal\drift_eleven\Core\Asserters;

interface PasswordAsserterInterface {
  public function assertPasswordRequirements(string $password): bool;

  public function assertPasswordMatch(string $password, string $confirmPassword): bool;
}
