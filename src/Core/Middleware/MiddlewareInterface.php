<?php

namespace Drupal\drift_eleven\Core\Middleware;

use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Symfony\Component\HttpFoundation\Request;

interface MiddlewareInterface {
  public const string AUTH = 'auth';
  public const string AUTH_ANONYM = 'auth_anonym';
  public const string AUTH_REFRESH = 'auth_refresh';
  public const string REQUEST = 'request';
  /**
   * List of allowed middleware types
   * @var array
   */
  public const array ALLOWED_MIDDLEWARES = [
    self::AUTH,
    self::AUTH_ANONYM,
    self::AUTH_REFRESH,
    self::REQUEST,
  ];

  public function __construct(Request $request, array $route = []);

  public function run(): ?Reply;
}
