<?php

namespace Drupal\drift_eleven\Core\Route;

interface  RouteBuilderInterface {
  /**
   * Builds a route and returns it
   * @param string $filePath filepath of the route
   * @return Route
   */
  public static function build(string $filePath): Route;
}
