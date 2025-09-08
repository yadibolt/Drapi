<?php

namespace Drupal\drift_eleven\Core\Asserters;

use Drupal\drift_eleven\Core\Route\Route;

interface RouteAsserterInterface {
  /**
   * Asserts that a given value meets certain criteria
   *
   * @param Route $route - the value to be asserted
   * @return bool - true if the value passes the assertion, false otherwise
   */
  public static function assert(Route $route): bool;
}
