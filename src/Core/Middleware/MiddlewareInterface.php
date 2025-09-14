<?php

namespace Drupal\drift_eleven\Core\Middleware;

use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Symfony\Component\HttpFoundation\Request;

interface MiddlewareInterface {
  public const string AUTH = 'auth';
  public const string AUTH_ANONYM = 'auth_anonym';
  public const string AUTH_REFRESH = 'auth_refresh';
  public const string REQUEST = 'request';
  public const string BODY_JSON = 'body_json';
  public const string BODY_BINARY = 'body_binary';
  /**
   * List of allowed middleware types
   * @var array
   */
  public const array ALLOWED_MIDDLEWARES = [
    self::AUTH,
    self::AUTH_ANONYM,
    self::AUTH_REFRESH,
    self::REQUEST,
    self::BODY_JSON,
    self::BODY_BINARY,
  ];

  public function __construct(Request $request, array $route = []);

  public function run(): ?Reply;
}
