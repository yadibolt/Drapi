<?php

namespace Drupal\drapi\Core\Http\Middleware\Interface;

use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Route;

interface MiddlewareInterface {
  public static function make(Route $route): self;
  public static function getId(): string;
  public function process(): ?Reply;
}
