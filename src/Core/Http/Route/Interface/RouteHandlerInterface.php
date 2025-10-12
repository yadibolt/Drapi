<?php

namespace Drupal\drift_eleven\Core\Http\Route\Interface;

use Drupal\drift_eleven\Core\Http\Reply;
use Symfony\Component\HttpFoundation\Request;

interface RouteHandlerInterface {
  public function handle(): Reply;
  public function init(Request $request): Reply;
  public function setCacheTags(array $tags): void;
}
