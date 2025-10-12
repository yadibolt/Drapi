<?php

namespace Drupal\drift_eleven\Core\Http\Route\Asserters\Interface;

use Drupal\drift_eleven\Core\Http\Route\Route;

interface RouteAsserterInterface {
  public static function assert(Route $route): bool;
}
