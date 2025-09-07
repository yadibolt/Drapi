<?php

namespace Drupal\pingvin\Asserter;

class UsernameAsserter {
  // todo: maybe make these configurable via config?
  /**
   * Asserts that a given username is valid.
   *
   * A valid username must:
   * - Be between 3 and 30 characters long.
   * - Contain only alphanumeric characters, underscores, and periods.
   * - Not start or end with an underscore or period.
   * - Not contain consecutive underscores or periods.
   *
   * @param string $username
   *   The username to validate.
   *
   * @return bool
   *   true if the username is valid, false otherwise.
   */
  public static function assert(string $username): bool {
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
}
