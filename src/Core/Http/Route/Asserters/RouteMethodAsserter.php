<?php

namespace Drupal\drift_eleven\Core\Http\Route\Asserters;

use Drupal\drift_eleven\Core\Http\Route\Asserters\Interface\RouteAsserterInterface;

class RouteMethodAsserter implements RouteAsserterInterface {
  public static function assert($route): bool {
    // 1, Class must have a 'handle' method
    if (!in_array('handle', $route->getClassPublicMethods())) return false;

    return true;
  }
}
