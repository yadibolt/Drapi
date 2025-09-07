<?php

namespace Drupal\pingvin\Addon\Core\Auth\Token;

use Drupal\pingvin\Auth\JsonWebToken;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;


/**
 * @route
 * id = 'pingvin:auth_token'
 * name = 'Pingvin Auth - Token'
 * method = 'GET'
 * description = 'Token route for Pingvin Auth.'
 * path = 'auth/token'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * @route-end
 */
class Token implements RouteInterface {
  /**
   * Handles Token requests.
   *
   * @param Request $request
   *    The HTTP request object from Symfony.
   * @return ServerJsonResponse
   *    The JSON response containing the login data.
   * @throws Exception
   *    Only if the middleware specifications are incorrect.
   */
  public function get(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['client:cors', 'request']);
    if ($request instanceof ServerJsonResponse) return $request;

    $jwt = new JsonWebToken();
    $payload = [
      'userId' => 0,
    ];

    // we return the access token, which is permanent and never expires
    // because it's for the anonymous user. we also do not create a session
    // for the anonymous user. we do not really care about its security
    // it does not contain any sensitive information or permissions.
    $accessToken = $jwt->create(JsonWebToken::TOKEN_TYPE_ACCESS, $payload, JsonWebToken::PERMANENT);

    return new ServerJsonResponse([
      'message' => 'Token generated successfully.',
      'accessToken' => $accessToken,
    ], 201);
  }
}
