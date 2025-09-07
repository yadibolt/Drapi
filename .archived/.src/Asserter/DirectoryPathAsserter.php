<?php

namespace Drupal\pingvin\Asserter;

class DirectoryPathAsserter {
  public static function assert(string $path): bool {
    if (is_numeric(str_split($path)[0])) {
      return false;
    }

    if (str_starts_with('/', $path)) {
      return false;
    }

    if (str_ends_with('/', $path)) {
      return false;
    }

    if (!preg_match('/^[A-Za-z0-9]+$/', $path)) {
      return false;
    }

    return true;
  }
}
