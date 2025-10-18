<?php

namespace Drupal\drapi\Core\Http\Route\Asserters\Interface;

use Drupal\drapi\Core\Http\Route\Route;

interface RouteAsserterInterface {
  public static function assert(Route $route): bool;
}
