<?php

namespace Drupal\pingvin\Addon\Routes;

use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Drupal\user\Entity\User;
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
  public function get(Request $request): ServerJsonResponse {
    // define the middleware to be used for this route
    // Middleware returns a $request with additional attributes
    // that are useful for the route.
    $request = Middleware::enable($request, ['auth:jwt']);

    // request has an error, when ServerJsonResponse is returned
    // after the middleware is applied, so we return the $request
    if ($request instanceof ServerJsonResponse) return $request;

    // if you are using 'auth:jwt' middleware
    // you can use the context attribute that exposes
    // various, useful information about the User for example.
    $context = $request->attributes->get('context');

    /* @var User|null $context */
    $user = $context['user'] ?: null;

    // this attribute is already processed by the middleware in JSON format
    // so you do not have to use json_decode on it.
    // if you are using POST method or other, that utilizes request body,
    // you can access the request data like this:

    /* @var array $data */
    $data = $context['data'] ?: [];

    // example of processing the request url parameters
    $someId = $context['some_id'] ?: null;

    // at last, we return a ServerJsonResponse
    // this response will be modified by the constructor
    // and returned to the client
    // if you provide 'message' in the response, it will be used in the response body.
    // pther attributes will be added to the 'data' attribute of the response.
    // example reponse from this route will look like this:
    // {
    //   "message": "Example route response",
    //   "error": false,
    //   "timestamp": 1678901234,
    //   "data": {
    //     "userId": 1,
    //     "someId": "example_id"
    //   }
    // }
    return new ServerJsonResponse([
      'message' => 'Example route response',
      'userId' => $user ? $user->id() : null,
      'someId' => $someId,
    ], 200);
  }
}
