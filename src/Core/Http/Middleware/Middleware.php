<?php

namespace Drupal\drift_eleven\Core\Http\Middleware;

use Drupal\drift_eleven\Core\Http\Middleware\Base\MiddlewareBase;
use Drupal\drift_eleven\Core\Http\Middleware\Interface\MiddlewareInterface;
use Drupal\drift_eleven\Core\Http\Reply;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class Middleware extends MiddlewareBase {
  public const array AVAILABLE_MIDDLEWARES = [
    'auth', 'request',
  ];
  protected array $middlewares = [
    AuthMiddleware::class,
    RequestMiddleware::class,
  ];

  public function __construct() {
    parent::__construct();
  }

  public static function make(): self {
    return new self();
  }

  public function apply(array $route): ?Reply {
    $routeMiddlewares = $route['use_middleware'] ?? [];

    foreach ($this->middlewares as $mw) {
      $instance = new $mw();

      if (!($instance instanceof MiddlewareInterface)) {
        throw new InvalidArgumentException('Middleware ' . get_class($instance) . ' must implement MiddlewareInterface');
      }

      $instance = $mw::make();
      if (in_array($instance::getId(), $routeMiddlewares)) {
        $result = $instance->process();
        if ($result !== null) return $result;
      }
    }

    return null;
  }
}
