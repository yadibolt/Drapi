<?php

namespace Drupal\pingvin\Addon\Core\Node;

use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id = 'pingvin:api_node'
 * name = 'Pingvin - Node'
 * method = 'GET'
 * description = 'Node'
 * path = 'api/node'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * cacheable = true
 * @route-end
 */
class Node implements RouteInterface {
  public function get(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['request']);
    if ($request instanceof ServerJsonResponse) return $request;

    /** @var array $context */
    $context = $request->attributes->get('context');

    return new ServerJsonResponse([
      'title' => "Hello World!",
    ], 200, $request);
  }
}
