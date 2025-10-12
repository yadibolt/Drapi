<?php

namespace Drupal\drift_eleven\Core2\Http\Route\Asserters;

use Drupal\drift_eleven\Core2\Http\Route\Route;
use Drupal\drift_eleven\Core2\Http\Route\Asserters\Interface\RouteAsserterInterface;

class RouteExtendsAsserter implements RouteAsserterInterface {
  public static function assert(Route $route): bool {
    // 1. Class must extend a parent class
    if (empty($route->getClassParentClass())) return false;

    // 2. Parent class must be 'RouteHandlerBase'
    if ($route->getClassParentClass() !== 'Drupal\drift_eleven\Core2\Http\Route\Base\RouteHandlerBase') return false;

    return true;
  }
}
