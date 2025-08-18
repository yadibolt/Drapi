<?php

namespace Drupal\pingvin\Middleware\Client;

use Drupal\pingvin\Http\ServerJsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CorsMiddleware {
  /**
   * The current request object.
   *
   * @var Request
   */
  protected Request $request;
  /**
   * The route definition array.
   *
   * This array contains the route information.
   * @see \Drupal\pingvin\Parser\RouteDocCommentParser
   * @var array
   */
  protected array $routeDefinition;

  /**
   * Constructs the AuthMiddleware.
   *
   * @param Request $request
   *    The current request object.
   * @param array $routeDefinition
   *    The route definition array.
   */
  public function __construct(Request $request, array $routeDefinition = []) {
    $this->request = $request;
    $this->routeDefinition = $routeDefinition;
  }

  /**
   * Applies the middleware to the request.
   * Middleware should be called at the very beginning of the request lifecycle.
   *
   * @return array|ServerJsonResponse
   *    Returns the attributes or a JSON response in case of an error.
   */
  public function apply(): array|ServerJsonResponse {
    return [
      'auth' => 'test3',
    ];
  }
}
