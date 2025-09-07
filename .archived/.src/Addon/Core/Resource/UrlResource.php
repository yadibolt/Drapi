<?php

namespace Drupal\pingvin\Addon\Core\Resource;
use Drupal\Core\Controller\ControllerBase;
use Drupal\pingvin\Http\PingvinResponse;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id = 'pingvin:api_url_resource'
 * name = 'Pingvin - UrlResource'
 * method = 'GET'
 * description = 'UrlResource'
 * path = 'api/resource'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * cacheable = true
 * @route-end
 */
class UrlResource extends ControllerBase implements RouteInterface {
  public function get(Request $request): PingvinResponse
  {
    $request = Middleware::enable($request, ['request']);
    if ($request instanceof PingvinResponse) return $request;

    /** @var array $context */
    $context = $request->attributes->get('context');
    /** @var ?array $urlResource */
    $urlResource = $context['urlResource'];

    return new PingvinResponse([
      'title' => "Hello World!",
    ], 200);
  }
}
