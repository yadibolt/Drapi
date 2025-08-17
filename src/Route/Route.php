<?php

namespace Drupal\pingvin\Route;

class Route {
  /**
   * The allowed route methods for the route.
   * These methods are used to define the HTTP methods that the routes supports.
   *
   * @var array
   */
  public const array ALLOWED_ROUTE_METHODS = [
    'GET',
    'POST',
    'PUT',
    'PATCH',
    'DELETE',
  ];
}
