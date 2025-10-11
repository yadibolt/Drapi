<?php

namespace Drupal\drift_eleven\Core2\Http\Middleware\Base;

use Drupal\drift_eleven\Core2\Http\Trait\RequestTrait;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareBase {
  use RequestTrait;

  protected Request $currentRequest;
  protected array $currentRoute = [];

  public function __construct() {
    $this->currentRequest = $this->getCurrentRequest();
    $this->currentRoute = $this->getCurrentRoute();
  }
}
