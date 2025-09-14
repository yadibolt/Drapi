<?php

namespace Drupal\drift_eleven\Core\Routes;

use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\drift_eleven\Core\Route\RouteFoundationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id= 'drift_eleven:auth:login'
 * name= 'Drift Eleven - Login Route'
 * method= 'POST'
 * description= 'An Example Drift Eleven Route'
 * path= 'example/route/{random_number}'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_anonym', 'body_json', 'request']
 * @route-end
 */
class LoginRoute extends RouteFoundation implements RouteFoundationInterface {
  public function handle(Request $request): Reply {
    return new Reply([
      'message' => 'Yay!',
      'someData' => [
        'hello' => 1
      ],
    ], 200);
  }
}
