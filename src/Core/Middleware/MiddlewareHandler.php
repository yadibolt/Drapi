<?php

namespace Drupal\drift_eleven\Core\Middleware;

use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Middleware\Auth\AuthAnonymMiddleware;
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
      if (!in_array($mw, MiddlewareInterface::ALLOWED_MIDDLEWARES)) {
        return new Reply([
          'message' => 'Invalid middleware: ' . $mw
        ], 500);
      }

      $result = match ($mw) {
        MiddlewareInterface::AUTH => new AuthMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::AUTH_ANONYM => new AuthAnonymMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::AUTH_REFRESH => new AuthRefreshMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::BODY_JSON => new JsonBodyMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::BODY_BINARY => new BinaryBodyMiddleware($this->request, $this->route)->run(),
        MiddlewareInterface::REQUEST => new RequestMiddleware($this->request, $this->route)->run(),
        default => new Reply([
          'message' => 'Unknown middleware: ' . $mw
        ], 500),
      };

      if ($result instanceof Reply) {
        return $result;
      }
    }

    return null;
  }
}
