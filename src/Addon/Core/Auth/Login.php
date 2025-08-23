<?php

namespace Drupal\pingvin\Addon\Core\Auth;

use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id = 'pingvin:auth_login'
 * name = 'Pingvin Auth - Login'
 * method = 'POST'
 * description = 'Login route for Pingvin Auth.'
 * path = 'auth/login'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * @route-end
 */
class Login implements RouteInterface {
  /**
   * Handles Login requests.
   *
   * @param Request $request
   *    The HTTP request object from Symfony.
   * @return ServerJsonResponse
   *    The JSON response containing the login data.
   * @throws Exception
   *    Only if the middleware specifications are incorrect.
   */
  public function post(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['body:json', 'client:cors', 'request']);
    if ($request instanceof ServerJsonResponse) return $request;

    /** @var array $context */
    $context = $request->attributes->get('context');

    return new ServerJsonResponse([
      'd' => $context['data'],
    ], 200);
  }
}
