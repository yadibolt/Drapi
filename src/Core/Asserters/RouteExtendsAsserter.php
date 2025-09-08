<?php

namespace Drupal\drift_eleven\Core\Asserters;

use Drupal\drift_eleven\Core\Route\Route;

class RouteExtendsAsserter implements RouteAsserterInterface {
  public static function assert(Route $route): bool {
    $attributes = $route->getFileAttributes();

    // 1. Class must extend a parent class
    if (empty($attributes['parentClass'])) return false;

    // 2. Parent class must be 'RouteFoundation'
    if ($attributes['parentClass'] !== 'Drupal\drift_eleven\Core\Route\RouteFoundation') return false;

    return true;
  }
}
