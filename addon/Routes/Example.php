<?php

namespace Drupal\pingvin\addon\Routes;

use Drupal\pingvin\Route\RouteInterface;

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
 * id = 'pingvin.example'
 * name = 'Example Route'
 * method = 'GET'
 * description = 'Excample route using GET!'
 * path = 'example/read'
 * permission = [
 *  'access content'
 * ]
 * restrict_host = []
 * enable_cache = false
 * @route-end
 */
class Example implements RouteInterface {

}
