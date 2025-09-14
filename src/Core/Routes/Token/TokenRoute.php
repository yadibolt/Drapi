<?php

namespace Drupal\drift_eleven\Core\Routes\Token;

use Drupal\drift_eleven\Core\Auth\JsonWebToken;
use Drupal\drift_eleven\Core\Auth\JsonWebTokenInterface;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\drift_eleven\Core\Route\RouteFoundationInterface;
use Symfony\Component\HttpFoundation\Request;

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
class TokenRoute extends RouteFoundation implements RouteFoundationInterface {
  public function handle(Request $request): Reply {
    $jsonWebToken = new JsonWebToken();
    $payload = [
      'userId' => 0,
    ];

    // generate permanent token
    $token = $jsonWebToken->make(JsonWebTokenInterface::TOKEN_ACCESS, $payload, JsonWebTokenInterface::EXP_TIME_PERMANENT_DEFAULT);

    return new Reply([
      'message' => 'Token generated.',
      'accessToken' => $token,
    ], 200);
  }
}
