<?php

namespace Drupal\pingvin\Addon\Core\Resource;

use Drupal\pingvin\Route\RouteInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id = 'pingvin:api_bundle'
 * name = 'Pingvin - Bundle'
 * method = 'GET'
 * description = 'Bundle'
 * path = 'api/bundle'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * cacheable = true
 * @route-end
 */
class Bundle implements RouteInterface {
  public function get(Request $request) {

  }
}
