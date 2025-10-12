<?php

namespace Drupal\drift_eleven\Core2\Http\Route\Asserters;

use Drupal\drift_eleven\Core2\Http\Route\Route;
use Drupal\drift_eleven\Core2\Http\Route\Asserters\Interface\RouteAsserterInterface;

class RouteImplementsAsserter implements RouteAsserterInterface {
  public static function assert(Route $route): bool {
    // 1. Class must implement RouteHandlerInterface
    if (!in_array('Drupal\drift_eleven\Core2\Http\Route\Interface\RouteHandlerInterface', $route->getClassInterfaces())) return false;

    return true;
  }

}
