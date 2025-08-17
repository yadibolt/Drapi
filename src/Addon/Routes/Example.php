<?php

namespace Drupal\pingvin\Addon\Routes;

use Drupal\pingvin\Route\RouteInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *  Example route implementation.
 *
 *  Each route file must have a class that has the same name as the file
 *  and implements the RouteInterface.
 *
 *  Allowed HTTP methods for routes are defined via configuration.
 *  Depending on the method, you have to define the corresponding method in the class.
 *  For example, if you want to handle GET requests, you have to define the `get` method.
 *
 * @route
 * id = 'pingvin:example'
 * name = 'Example Route'
 * method = 'GET'
 * description = 'Example route using GET!'
 * path = 'example/read'
 * permission = [
 *  'access content'
 * ]
 * @route-end
 */
class Example implements RouteInterface {
  public function get(Request $request): JsonResponse {
    return new JsonResponse([
      'message' => 'Hello from the other side!'
    ], 200);
  }
}
