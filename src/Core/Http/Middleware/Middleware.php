<?php

namespace Drupal\drapi\Core\Http\Middleware;

use Drupal\drapi\Core\Http\Middleware\Base\MiddlewareBase;
use Drupal\drapi\Core\Http\Middleware\Interface\MiddlewareInterface;
use Drupal\drapi\Core\Http\Reply;
use InvalidArgumentException;

class Middleware extends MiddlewareBase {
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
