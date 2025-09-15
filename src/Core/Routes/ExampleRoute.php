<?php

namespace Drupal\drift_eleven\Core\Routes;

use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;

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
 * useMiddleware= ['request']
 * useCache= true
 * @route-end
 */
class ExampleRoute extends RouteFoundation {
  public function handle(): Reply {
    return new Reply([
      'message' => 'Yay!',
      'someData' => [
        'hello' => 1
      ],
    ], 200);
  }
}
