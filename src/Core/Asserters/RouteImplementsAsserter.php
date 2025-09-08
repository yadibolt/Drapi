<?php

namespace Drupal\drift_eleven\Core\Asserters;

use Drupal\drift_eleven\Core\Route\Route;

class RouteImplementsAsserter implements RouteAsserterInterface {
  public static function assert(Route $route): bool {
    $attributes = $route->getFileAttributes();

    // 1. Class must implement RouteFoundationInterface
    if (!in_array('Drupal\drift_eleven\Core\Route\RouteFoundationInterface', $attributes['interfaces'])) return false;

    return true;
  }
}
