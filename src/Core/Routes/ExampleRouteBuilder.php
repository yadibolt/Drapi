<?php

namespace Drupal\drift_eleven\Core\Routes;

use Drupal\drift_eleven\Core\HTTP\Reply;
use Drupal\drift_eleven\Core\Route\RouteBuilder;
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
 *   = true
 * @route-end
 */
class ExampleRouteBuilder extends RouteBuilder {
  public function get(Request $request): Reply {
    return new Reply([
      'message' => 'Yay!',
      'someData' => [
        'hello' => 1
      ],
    ], 200);
  }
}
