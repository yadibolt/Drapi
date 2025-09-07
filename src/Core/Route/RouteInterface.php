<?php

namespace Drupal\drift_eleven\Core\Route;

interface RouteInterface {
  /**
   * Allowed HTTP methods that route can define
   * @var array
   */
  public const array ALLOWED_HTTP_METHODS = [
    'GET',
    'POST',
    'PUT',
    'PATCH',
    'DELETE',
  ];
  /**
   * Allowed schemes for communication
   * @var array
   */
  public const array ALLOWED_SCHEMES = [
    'http', 'https'
  ];

  /**force-commit
   * Constructs a route
   *
   * @param string $id id of the route
   * @param string $name name of the route
   * @param string $method method that route uses
   * @param string $description description for the route
   * @param string $path endpoint path for route
   * @param array $permissions permissions that are required to access this route
   * @param array $roles roles that are required to access this route
   * @param array $useMiddleware array of strings (middlewares) that should run before the route hit
   * @param bool $useCache if the route response should be cached for next requests
   * @param string $filePath path to the file where the route is defined
   */
  public function __construct(string $id, string $name, string $method, string $description, string $path, array $permissions, array $roles, array $useMiddleware, bool $useCache, string $filePath = '');
}
