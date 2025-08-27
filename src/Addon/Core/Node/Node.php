<?php

namespace Drupal\pingvin\Addon\Core\Node;

use Drupal\Core\Path\PathMatcherInterface;
use Drupal\path_alias\AliasManagerInterface;
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
 * @route-end
 */
class Node implements RouteInterface {
  public function get(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['request']);
    if ($request instanceof ServerJsonResponse) return $request;

    /** @var array $context */
    $context = $request->attributes->get('context');
    $queryUrl = $request->query->get('tar');
    $queryLang = $request->query->get('lang');

    $internal_path = \Drupal::service('path_alias.manager')->getPathByAlias($queryUrl, $queryLang);
    $nodeId = (int)str_replace('/node/', '', $internal_path);

    $node = \Drupal\node\Entity\Node::load($nodeId);

    return new ServerJsonResponse([
      'data' => $internal_path === $queryUrl ? [] : $nodeId
    ], 200);
  }
}
