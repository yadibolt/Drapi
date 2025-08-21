<?php

namespace Drupal\pingvin\Addon\Core\Auth;

use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id = 'pingvin:auth_login'
 * name = 'Pingvin Auth - Login'
 * method = 'POST'
 * description = 'Login route for Pingvin Auth.'
 * path = 'auth/login'
 * permission = [
 *  'access content'
 * ]
 * @route-end
 */
class Login implements RouteInterface {
  public function post(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['body:json', 'client:cors', 'request']);
    if ($request instanceof ServerJsonResponse) return $request;

    $context = $request->attributes->get('context');

    return new ServerJsonResponse([
      'd' => $context['data'],
    ], 200);
  }
}
