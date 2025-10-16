<?php

namespace Drupal\drift_eleven\Routes\Auth\Token;

use Drupal\drift_eleven\Core\Auth\Enum\JWTIntent;
use Drupal\drift_eleven\Core\Auth\JWT;
use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandler;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drift_eleven\Core\Http\Route\Interface\RouteHandlerInterface;
use Drupal\drift_eleven\Core\Session\Enum\SubjectIntent;

#[RouteHandler(
  id: 'auth:token:token',
  name: '(Core) Token Route',
  method: 'GET',
  path: 'auth/token',
  description: 'Route to get a token that is used for authorization',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request'],
  useCache: false
)]
class TokenRoute extends RouteHandlerBase implements RouteHandlerInterface {
  public function handle(): Reply {
    $token = JWT::make(JWTIntent::ACCESS_TOKEN_UNLIMITED, [
      'user_id' => 0,
      'type' => SubjectIntent::ANONYMOUS,
    ]);

    return Reply::make([
      'message' => 'Token generated successfully.',
      'token' => $token
    ]);
  }
}
