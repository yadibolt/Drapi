<?php

namespace Drupal\drift_eleven\Core\Middleware;

use Drupal\drift_eleven\Core\HTTP\Reply;
use Drupal\drift_eleven\Core\Middleware\Auth\AuthMiddleware;
use Drupal\drift_eleven\Core\Middleware\Auth\AuthRefreshMiddleware;
use Drupal\drift_eleven\Core\Middleware\Request\BinaryBodyMiddleware;
use Drupal\drift_eleven\Core\Middleware\Request\JsonBodyMiddleware;
use Drupal\drift_eleven\Core\Middleware\Request\RequestMiddleware;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareHandler {
  protected Request $request;
  protected array $route;
  protected array $middlewares = [];

  public function __construct(Request $request, array $route, array $middlewares = []) {
    $this->request = $request;
    $this->route = $route;
    $this->middlewares = $middlewares;
  }

  public function handle(): ?Reply {
    foreach ($this->middlewares as $mw) {
      $result = match ($mw) {
        MiddlewareInterface::AUTH => new AuthMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::AUTH_REFRESH => new AuthRefreshMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::BODY_JSON => new JsonBodyMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::BODY_BINARY => new BinaryBodyMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::REQUEST => new RequestMiddleware($this->request, $this->route)->run(),
      };

      if ($result instanceof Reply) {
        return $result;
      }
    }

    return null;
  }
}
