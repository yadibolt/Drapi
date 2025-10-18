<?php

namespace Drupal\drapi\Core\Http\Middleware\Interface;

use Drupal\drapi\Core\Http\Reply;

interface MiddlewareInterface {
  public static function make(): self;
  public static function getId(): string;
  public function process(): ?Reply;
}
