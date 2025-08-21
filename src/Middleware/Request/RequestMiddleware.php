<?php

namespace Drupal\pingvin\Middleware\Request;

use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Sanitizer\InputSanitizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RequestMiddleware {
  /**
   * Suspicious User-Agent strings to block.
   *
   * @var array
   */
  protected const array SUSPICIOUS_AGENTS = [];
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
    if ((!isset($this->server['HTTPS']) || $this->server['HTTPS'] !== 'on') &&
      (!isset($this->server['REQUEST_SCHEME']) || $this->server['REQUEST_SCHEME'] !== 'https') &&
      (!isset($this->server['SERVER_PORT']) || $this->server['SERVER_PORT'] != 443)) {
      return new ServerJsonResponse([
        'message' => 'Invalid protocol.',
      ], 400);
    }

    if (empty($this->headers)) {
      return new ServerJsonResponse([
        'message' => 'Headers cannot be empty.',
      ], 400);
    }

    if (!empty(self::SUSPICIOUS_AGENTS)) {
      if (array_any(self::SUSPICIOUS_AGENTS, fn($agent) => stripos($this->request->headers['user-agent'][0], $agent) !== false)) {
        return new ServerJsonResponse([
          'message' => 'Suspicious User-Agent detected.',
        ], 400);
      }
    }

    return [];
  }
}
