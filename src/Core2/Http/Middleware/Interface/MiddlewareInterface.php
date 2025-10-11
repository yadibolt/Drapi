<?php

namespace Drupal\drift_eleven\Core2\Http\Middleware\Interface;

use Drupal\drift_eleven\Core2\Http\Reply;

interface MiddlewareInterface {
  public static function make(): self;
  public function getId(): string;
  public function process(): ?Reply;
}
