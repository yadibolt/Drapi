<?php

namespace Drupal\drift_eleven\Core\Asserters;

class PasswordAsserter implements PasswordAsserterInterface {
  public function assertPasswordRequirements(string $password): bool {
    // TODO: change the pattern
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password) === 1;
  }

  public function assertPasswordMatch(string $password, string $confirmPassword): bool {
    return $password === $confirmPassword;
  }
}
