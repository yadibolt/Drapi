<?php

namespace Drupal\drapi\Core\Http\Middleware\Base;

use Drupal\drapi\Core\Http\Route\Route;
use Drupal\drapi\Core\Http\Trait\RequestTrait;
use Symfony\Component\HttpFoundation\Request;

abstract class MiddlewareBase {
  use RequestTrait;

  protected Request $currentRequest;
  protected Route $currentRoute;

  public function __construct(Route $route) {
    $this->currentRoute = $route;
    $this->currentRequest = $this->getCurrentRequest();
  }
}
