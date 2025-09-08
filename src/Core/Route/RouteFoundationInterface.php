<?php

namespace Drupal\drift_eleven\Core\Route;

use Drupal\drift_eleven\Core\HTTP\Reply;
use Symfony\Component\HttpFoundation\Request;

interface RouteFoundationInterface {
  public function handle(Request $request): Reply;
}
