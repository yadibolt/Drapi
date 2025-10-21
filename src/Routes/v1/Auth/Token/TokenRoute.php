<?php

namespace Drupal\drapi\Routes\v1\Auth\Token;

use Drupal\drapi\Core\Auth\Enum\JWTIntent;
use Drupal\drapi\Core\Auth\JWT;
use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Base\RouteHandler;
use Drupal\drapi\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drapi\Core\Session\Enum\SubjectIntent;

#[RouteHandler(
  id: 'auth:token:token',
  name: '[DrapiCore] Token Route',
  method: 'GET',
  path: 'v1/auth/token',
  description: 'Route to get a token that is used for authorization',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request'],
  useCache: false
)]
class TokenRoute extends RouteHandlerBase {
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
