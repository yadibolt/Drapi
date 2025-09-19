<?php

namespace Drupal\drift_eleven\Core\Asserters;

class UserAsserter implements UserAsserterInterface {
  public function assertUsernameRequirements(string $username): bool {
    if (strlen($username) < 3 || strlen($username) > 30) {
      return false;
    }

    if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
      return false;
    }

    if (preg_match('/^[_\.]|[_\.]$/', $username)) {
      return false;
    }

    if (preg_match('/[_\.]{2,}/', $username)) {
      return false;
    }

    return true;
  }

  public function assertPasswordRequirements(string $password): bool {
    // TODO: change the pattern
    if (strlen($password) < 12) {
      return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
      return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
      return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
      return false;
    }
    if (!preg_match('/[\W_]/', $password)) {
      return false;
    }
    return true;
  }

  public function assertPasswordMatch(string $password, string $confirmPassword): bool {
    return $password === $confirmPassword;
  }
}
