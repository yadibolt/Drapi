<?php

namespace Drupal\drift_eleven\Core\Routes;

use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;

/**
 * **Example Route Definition**
 * - todo add description
 * @route
 * id= 'drift_eleven:example_protected_route'
 * name= 'Drift Eleven Example Route'
 * method= 'GET'
 * description= 'An Example Drift Eleven Route'
 * path= 'example/route/{random_number}/protected'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth', 'request']
 * useCache= true
 * @route-end
 */
class ExampleProtectedRoute extends RouteFoundation {
  public function handle(): Reply {
    return new Reply([
      'message' => 'Protected!',
      'someData' => [
        'hello' => 1
      ],
    ], 200);
  }
}
