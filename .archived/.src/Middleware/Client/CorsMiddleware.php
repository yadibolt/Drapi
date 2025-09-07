<?php

namespace Drupal\pingvin\Middleware\Client;

use Drupal\pingvin\Http\ServerJsonResponse;
use Grpc\Server;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware {
  /**
   * Allowed origins for CORS requests.
   *
   * This constant defines the allowed origins for CORS requests.
   * It can be set to '*' to allow all origins or a specific origin URL.
   */
  public const string ALLOW_ORIGINS = '*';

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
  public function apply(): array|ServerJsonResponse|Response {
    // preflight
    if ($this->request->getMethod() === 'OPTIONS') {
      $response = new Response('', 204);
      $response->headers->set('Access-Control-Allow-Origin', self::ALLOW_ORIGINS);
      $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
      $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
      $response->headers->set('Access-Control-Expose-Headers', 'Content-Type, Authorization');

      return $response;
    }

    return [];
  }
}
