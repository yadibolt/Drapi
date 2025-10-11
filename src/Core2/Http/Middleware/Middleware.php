<?php

namespace Drupal\drift_eleven\Core2\Http\Middleware;

use Drupal\drift_eleven\Core2\Http\Middleware\Base\MiddlewareBase;
use Drupal\drift_eleven\Core2\Http\Middleware\Interface\MiddlewareInterface;
use Drupal\drift_eleven\Core2\Http\Reply;
use http\Exception\InvalidArgumentException;

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

  public function apply(): ?Reply {
    $routeMiddlewares = $this->getCurrentRoute()['use_middleware'] ?? [];

    foreach ($this->middlewares as $mw) {
      if (!($mw instanceof MiddlewareInterface)) {
        throw new InvalidArgumentException('Middleware ' . get_class($mw) . ' must implement MiddlewareInterface');
      }

      if (in_array($mw->getId(), $routeMiddlewares)) {
        $middlewareResult = $mw->process();
        if ($middlewareResult !== null) return $middlewareResult;
      }
    }

    return null;
  }
}
