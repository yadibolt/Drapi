<?php

namespace Drupal\pingvin\Middleware\Request;

use Drupal;
use Drupal\pingvin\Http\PingvinResponse;
use Drupal\pingvin\Resolver\PathResolver;
use Drupal\pingvin\Sanitizer\InputSanitizer;
use Exception;
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
   * An associative array of entity
   * containing entity id and type
   * Can be null if not found
   *
   * @var ?array $urlResource
   */
  protected ?array $urlResource;

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
   * @return array|PingvinResponse
   *    Returns the attributes or a JSON response in case of an error.
   * @throws Exception
   */
  public function apply(): array|PingvinResponse {
    // setup cacheable property
    $this->request->headers->set('x-'.pw8dr1_PROJECT_ID.'-cacheable', $this->routeDefinition['cacheable']);

    $queryLoc = $this->request->query->get('urlLoc');
    if ($this->routeDefinition['cacheable']) {
      $hit = Drupal\pingvin\Route\Cache::hit($this->request, [$queryLoc]);
      if ($hit) return new PingvinResponse($hit['json'], $hit['status'], $hit['headers']);
    }



    /*// we also discover the type of the entity
    // and return its id as additional context
    // if it exists
    // todo: change this
    $queryLoc = $this->request->query->get('urlLoc');
    if ($queryLoc) {
      $resolvedEntity = PathResolver::entityFromAlias($queryLoc, $this->request->query->get('lang') ?: 'en');
      if ($resolvedEntity) {
        $this->request->headers->set('x-'.pw8dr1_PROJECT_ID.'-cacheable-context', "{$resolvedEntity['entityType']}:{$resolvedEntity['entityId']}");
        $this->urlResource = [
          'entityId' => $resolvedEntity['entityId'],
          'entityType' => $resolvedEntity['entityType'],
        ];

        // cache
        $hit = Drupal\pingvin\Route\Cache::hit($this->request, ["{$resolvedEntity['entityType']}:{$resolvedEntity['entityId']}"]);
        if (!empty($hit)) {
          return new PingvinResponse($hit);
        }
      }
    }*/

    if (
      ($this->request->server->get('HTTPS') === null || $this->request->server->get('HTTPS') !== 'on') &&
      ($this->request->server->get('REQUEST_SCHEME') === null || $this->request->server->get('REQUEST_SCHEME') !== 'https') &&
      ($this->request->server->get('SERVER_PORT') === null || (int)$this->request->server->get('SERVER_PORT') !== 443)
    ) {
      return new PingvinResponse([
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
      return new PingvinResponse([
        'message' => 'Headers cannot be empty.',
        'actionId' => 'headers:empty',
      ], 400);
    }

    if (!empty(self::SUSPICIOUS_AGENTS)) {
      if (array_any(self::SUSPICIOUS_AGENTS, fn($agent) => stripos($this->request->server->get('HTTP_USER_AGENT'), $agent) !== false)) {
        return new PingvinResponse([
          'message' => 'Suspicious User-Agent detected.',
          'actionId' => 'user_agent:not_allowed',
        ], 400);
      }
    }

    $agent = $this->request->server->get('HTTP_USER_AGENT') ?: 'unknown';
    $agent = substr($agent, 0, 512);

    return [
      'userAgent' => $agent,
      // 'urlResource' => $this->urlResource ?: null,
    ];
  }
}
