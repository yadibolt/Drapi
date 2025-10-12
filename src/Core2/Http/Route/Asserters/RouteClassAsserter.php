<?php

namespace Drupal\drift_eleven\Core2\Http\Route\Asserters;

use Drupal\drift_eleven\Core2\Http\Route\Asserters\Interface\RouteAsserterInterface;
use Drupal\drift_eleven\Core2\Http\Route\Route;

class RouteClassAsserter implements RouteAsserterInterface {

  public static function assert(Route $route): bool {
    // 1, ClassName must match file name
    if ($route->getClassShortName() !== pathinfo($route->getFilePath(), PATHINFO_FILENAME)) return false;

    // 2, ClassName must start with uppercase letter
    if (!ctype_upper(str_split($route->getClassShortName())[0])) return false;

    return true;
  }
}
