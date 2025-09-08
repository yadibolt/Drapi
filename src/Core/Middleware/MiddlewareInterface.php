<?php

namespace Drupal\drift_eleven\Core\Middleware;

interface MiddlewareInterface {
  /**
   * List of allowed middleware types
   * @var array
   */
  public const array ALLOWED_MIDDLEWARES = [
    'request',
    'auth',
    'auth_refresh',
    'body_json',
    'body_binary',
  ];
}
