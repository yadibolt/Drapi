<?php

namespace Drupal\drift_eleven\Core\Http\Middleware\Base;

use Drupal\drift_eleven\Core\Http\Route\Route;
use Drupal\drift_eleven\Core\Http\Trait\RequestTrait;
use Symfony\Component\HttpFoundation\Request;

abstract class MiddlewareBase {
  use RequestTrait;

  protected Request $currentRequest;
  protected Route $currentRoute;

  public function __construct() {
    $this->currentRequest = $this->getCurrentRequest();
    $this->currentRoute = $this->getCurrentRoute();
  }
}
