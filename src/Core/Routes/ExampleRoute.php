<?php

namespace Drupal\drift_eleven\Core\Routes;

use Drupal\drift_eleven\Core\HTTP\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\drift_eleven\Core\Route\RouteFoundationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * **Example Route Definition**
 * - todo add description
 * @route
 * id= 'drift_eleven:example'
 * name= 'Drift Eleven Example Route'
 * method= 'GET'
 * description= 'An Example Drift Eleven Route'
 * path= 'example/route/{random_number}'
 * permissions= ['access content']
 * roles= []
 * useMiddleware = ['request']
 * useCache= true
 * @route-end
 */
class ExampleRoute extends RouteFoundation implements RouteFoundationInterface {
  public function handle(Request $request): Reply {
    return new Reply([
      'message' => 'Yay!',
      'someData' => [
        'hello' => 1
      ],
    ], 200);
  }
}
