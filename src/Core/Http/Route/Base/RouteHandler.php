<?php

namespace Drupal\drift_eleven\Core\Http\Route\Base;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class RouteHandler {
  /**
   * @param string $id
   *  The route ID.
   * @param string $name
   *  The route name.
   * @param string $method
   *  The HTTP method (GET, POST, etc.).
   * @param string $description
   *  A brief description of the route.
   * @param string $path
   *  The URL path for the route. Cannot start with a leading slash.
   *  Uri params can be passed using curly braces, e.g. 'example/{param}'.
   * @param array<string> $permissions
   *  A string array of permissions required to access the route.
   * @param array<string> $roles
   *  A string array of roles required to access the route.
   * @param array<string> $useMiddleware
   *  A string array of middleware to apply to the route.
   * @param bool $useCache
   *  Whether to enable caching for the route.
   */
  public function __construct(
    public string $id,
    public string $name,
    public string $method,
    public string $path,
    public string $description = '',
    public array  $permissions = [],
    public array  $roles = [],
    public array  $useMiddleware = [],
    public bool   $useCache = false
  ) {}
}
