<?php

namespace Drupal\drapi\Core\Http\Route\Asserters;

use Drupal\drapi\Core\Http\Route\Route;
use Drupal\drapi\Core\Http\Route\Asserters\Interface\RouteAsserterInterface;

class RouteExtendsAsserter implements RouteAsserterInterface {
  public static function assert(Route $route): bool {
    // 1. Class must extend a parent class
    if (empty($route->getClassParentClass())) return false;

    // 2. Parent class must be 'RouteHandlerBase'
    if ($route->getClassParentClass() !== 'Drupal\drapi\Core\Http\Route\Base\RouteHandlerBase') return false;

    return true;
  }
}
