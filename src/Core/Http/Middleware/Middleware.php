<?php

namespace Drupal\drapi\Core\Http\Middleware;

use Drupal\drapi\Core\Http\Middleware\Base\MiddlewareBase;
use Drupal\drapi\Core\Http\Middleware\Interface\MiddlewareInterface;
use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Route;
use InvalidArgumentException;

class Middleware extends MiddlewareBase {
  protected array $middlewares = [
    AuthMiddleware::class,
    RequestMiddleware::class,
  ];

  public function __construct(Route $route) {
    parent::__construct($route);
  }

  public static function make(Route $route): self {
    return new self($route);
  }

  public function apply(): ?Reply {
    $routeMiddlewares = $this->currentRoute->getUseMiddleware() ?? [];

    foreach ($this->middlewares as $mw) {
      $instance = new $mw($this->currentRoute);

      if (!($instance instanceof MiddlewareInterface)) {
        throw new InvalidArgumentException('Middleware ' . get_class($instance) . ' must implement MiddlewareInterface');
      }

      $instance = $mw::make($this->currentRoute);
      if (in_array($instance::getId(), $routeMiddlewares)) {
        $result = $instance->process();
        if ($result !== null) return $result;
      }
    }

    return null;
  }
}
