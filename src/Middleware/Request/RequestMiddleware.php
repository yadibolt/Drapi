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
    if (
      ($this->request->server->get('HTTPS') === null || $this->request->server->get('HTTPS') !== 'on') &&
      ($this->request->server->get('REQUEST_SCHEME') === null || $this->request->server->get('REQUEST_SCHEME') !== 'https') &&
      ($this->request->server->get('SERVER_PORT') === null || (int)$this->request->server->get('SERVER_PORT') !== 443)
    ) {
      return new ServerJsonResponse([
        'message' => 'Invalid protocol.',
        'actionId' => 'protocol:invalid',
      ], 400);
    }

    if (!empty($this->request->query->all())) {
      foreach ($this->request->query->all() as $key => $value) {
        $inputSanitizer = new InputSanitizer($value);
        $this->request->query->set($key, $inputSanitizer->sanitize('xss'));
        $this->request->query->set($key, $inputSanitizer->sanitize('sql'));
      }
    }

    if (empty($this->request->headers)) {
      return new ServerJsonResponse([
        'message' => 'Headers cannot be empty.',
        'actionId' => 'headers:empty',
      ], 400);
    }

    if (!empty(self::SUSPICIOUS_AGENTS)) {
      if (array_any(self::SUSPICIOUS_AGENTS, fn($agent) => stripos($this->request->server->get('HTTP_USER_AGENT'), $agent) !== false)) {
        return new ServerJsonResponse([
          'message' => 'Suspicious User-Agent detected.',
          'actionId' => 'user_agent:not_allowed',
        ], 400);
      }
    }

    $agent = $this->request->server->get('HTTP_USER_AGENT') ?: 'unknown';
    $agent = substr($agent, 0, 512);

    return [
      'userAgent' => $agent,
    ];
  }
}
