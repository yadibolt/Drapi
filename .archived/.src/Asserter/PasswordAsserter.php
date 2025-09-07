<?php

namespace Drupal\pingvin\Asserter;

use Drupal\pingvin\Route\RouteFile;

class PasswordAsserter {
  // todo: maybe make these configurable via config?
  /**
   * Asserts that a password meets the following criteria:
   * - At least 12 characters long
   * - Contains at least one lowercase letter
   * - Contains at least one uppercase letter
   * - Contains at least one digit
   * - Contains at least one special character
   *
   * @param string $password
   *   The password to validate.
   * @return bool
   *   true if the password meets all criteria, false otherwise.
   */
  public static function assert(string $password): bool {
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
}
