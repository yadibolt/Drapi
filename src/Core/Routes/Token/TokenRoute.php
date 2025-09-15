<?php

namespace Drupal\drift_eleven\Core\Routes\Token;

use Drupal\drift_eleven\Core\Auth\JsonWebToken;
use Drupal\drift_eleven\Core\Auth\JsonWebTokenInterface;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;

/**
 * @route
 * id= 'drift_eleven:token:token'
 * name= 'Drift Eleven - Token Route'
 * method= 'GET'
 * description= 'Token route for Drift Eleven.'
 * path= 'auth/token'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['request']
 * useCache= false
 * @route-end
 */
class TokenRoute extends RouteFoundation {
  public function handle(): Reply {
    $jsonWebToken = new JsonWebToken();
    $payload = [
      'userId' => 0,
    ];

    // generate permanent token
    $tok = $jsonWebToken->make(JsonWebTokenInterface::TOKEN_ACCESS, $payload, JsonWebTokenInterface::EXP_TIME_PERMANENT_DEFAULT);

    return new Reply([
      'message' => 'Token generated.',
      'accessToken' => $tok,
    ], 200);
  }
}
