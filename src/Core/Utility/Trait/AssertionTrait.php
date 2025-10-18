<?php

namespace Drupal\drapi\Core\Utility\Trait;

trait AssertionTrait {
  protected function assertionForUsername(string $username): ?string {
    // 1. username must be between 5 and 24 characters
    if (strlen($username) < 5 || strlen($username) > 24) return null;

    // 2, username can only contain alphanumeric characters and underscores
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) return null;

    // 3. username cannot start or end with an underscore
    if (preg_match('/^_|_$/', $username)) return null;

    // 4. username cannot contain consecutive underscores
    if (preg_match('/__+/', $username)) return null;

    return $username;
  }

  protected function assertionForPassword(string $password): ?string {
    // 1. password must be at least 8 characters long
    if (strlen($password) < 8) return null;

    // 2. password must contain at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) return null;

    // 3. password must contain at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) return null;

    // 4. password must contain at least one digit
    if (!preg_match('/[0-9]/', $password)) return null;

    // 5. password must contain at least one special character
    if (!preg_match('/[\W_]/', $password)) return null;

    return $password;
  }
}
