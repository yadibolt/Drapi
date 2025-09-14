<?php

namespace Drupal\drift_eleven\Core\Middleware\Auth;

use Drupal\drift_eleven\Core\HTTP\Request\RequestAttributesTrait;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Middleware\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthRefreshMiddleware implements MiddlewareInterface {
  use RequestAttributesTrait;

  protected Request $request;
  protected array $route;

  public function __construct(Request $request, array $route = []) {
    $this->request = $request;
    $this->route = $route;
  }

  public function run(): ?Reply {
    return null;
  }
}
