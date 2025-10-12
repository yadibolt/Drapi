<?php

namespace Drupal\drift_eleven\Core\Http\Middleware\Interface;

use Drupal\drift_eleven\Core\Http\Reply;

interface MiddlewareInterface {
  public static function make(): self;
  public static function getId(): string;
  public function process(): ?Reply;
}
