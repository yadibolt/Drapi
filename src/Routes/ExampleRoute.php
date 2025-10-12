<?php

namespace Drupal\drift_eleven\Core2\Routes;


use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drift_eleven\Core\Http\Route\Interface\RouteHandlerInterface;

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
class ExampleRoute extends RouteHandlerBase implements RouteHandlerInterface {
  public function handle(): Reply {
    return Reply::make([
      'message' => 'Yay!'
    ], 200);
  }
}
