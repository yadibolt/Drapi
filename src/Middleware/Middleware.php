<?php

namespace Drupal\pingvin\Middleware;

use Drupal;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Middleware\Auth\AuthMiddleware;
use Drupal\pingvin\Middleware\Auth\AuthRefreshMiddleware;
use Drupal\pingvin\Middleware\Client\CorsMiddleware;
use Drupal\pingvin\Middleware\Request\JsonBodyMiddleware;
use Drupal\pingvin\Middleware\Request\RequestMiddleware;
use Symfony\Component\HttpFoundation\Request;

class Middleware {
  /**
   * The allowed middlewares for the route.
   * @var array
   */
  public const array ALLOWED_MIDDLEWARES = [
    'auth:jwt',
    'auth:jwt:refresh',
    'body:json',
    'client:cors',
    'request'
  ];

  /**
   * Enable the middleware for the route.
   * This method applies the middlewares based on the $middlewares parameter.
   *
   * @param Request $request
   *    The current request object.
   * @param array $middlewares
   *    An array of middlewares to apply. Each middleware should be a string
   * @return null|ServerJsonResponse
   *    Returns null if the request is valid and all middlewares passed,
   *    or a ServerJsonResponse if any middleware validation fails.
   */
  public static function enable(Request $request, array $middlewares = []): Request|ServerJsonResponse {
    $routeObject = $request->attributes->get('_route_object');
    if (!$routeObject) {
      return new ServerJsonResponse([
        'message' => 'No route attributes.'
      ], 400);
    }

    // extract the route ID from the route object,
    // so we can get the route definition from the config
    $routeId = $routeObject->getOption(pw8dr1_PROJECT_ID.':routeId');
    if (!$routeId) {
      return new ServerJsonResponse([
        'message' => 'No route ID found.'
      ], 404);
    }

    $configCtx = pw8dr1_PROJECT_ID . '.settings';
    $config = Drupal::configFactory()->getEditable($configCtx);
    $routeRegistry = $config->get('route_registry');

    if (!isset($routeRegistry[$routeId])) {
      return new ServerJsonResponse([
        'message' => 'Route not found in registry.'
      ], 404);
    }

    $attributes = [];
    $routeDefinition = $routeRegistry[$routeId];
    foreach ($middlewares as $middleware) {
      if (!in_array(strtolower($middleware), self::ALLOWED_MIDDLEWARES)) {
        return new ServerJsonResponse([
          'message' => "Middleware {$middleware} is not allowed."
        ], 400);
      }

      $response = match ($middleware) {
        'auth:jwt' => new AuthMiddleware($request, $routeDefinition)->apply(),
        'auth:jwt:refresh' => new AuthRefreshMiddleware($request, $routeDefinition)->apply(),
        'body:json' => new JsonBodyMiddleware($request, $routeDefinition)->apply(),
        'client:cors' => new CorsMiddleware($request, $routeDefinition)->apply(),
        'request' => new RequestMiddleware($request, $routeDefinition)->apply(),
        default => new ServerJsonResponse([
          'message' => "Middleware {$middleware} is not implemented."
        ], 501),
      };

      // we return ServerJsonResponse only if validation fails
      // otherwise we do not return anything, because Request $request
      // is already exposed in the route controller
      if ($response instanceof ServerJsonResponse) return $response;

      // we add new attributes that the middlewares might retrieved
      // if the keys are the same, we do not override the existing ones
      // this results in 'first in, first served' behavior
      if (!empty($response) && is_array($response)) {
        foreach ($response as $key => $value) {
          if (!isset($attributes[$key])) $attributes[$key] = $value;
        }
      }
    }

    // if evething is fine, we return the request back
    // with the attributes set by the middlewares
    if (!empty($attributes)) $request->attributes->set('context', $attributes);

    return $request;
  }
}
