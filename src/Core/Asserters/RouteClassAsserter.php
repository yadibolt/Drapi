<?php

namespace Drupal\drift_eleven\Core\Asserters;

use Drupal\drift_eleven\Core\Route\Route;

class RouteClassAsserter implements RouteAsserterInterface {
  public static function assert(Route $route): bool {
    $attributes = $route->getFileAttributes();

    // 1, ClassName must match file name
    if ($attributes['shortName'] !== pathinfo($route->getFilePath(), PATHINFO_FILENAME)) return false;

    // 2, ClassName must start with uppercase letter
    if (!ctype_upper(str_split($attributes['shortName'])[0])) return false;

    return true;
  }
}
