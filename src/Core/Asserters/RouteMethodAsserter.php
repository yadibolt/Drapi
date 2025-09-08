<?php

namespace Drupal\drift_eleven\Core\Asserters;

use Drupal\drift_eleven\Core\Route\Route;

class RouteMethodAsserter implements RouteAsserterInterface {
  public static function assert(Route $route): bool {
    $attributes = $route->getFileAttributes();

    // 1, Class must have a 'handle' method
    if (!in_array('handle', $attributes['publicMethods'])) return false;

    return true;
  }
}
