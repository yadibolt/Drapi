<?php

namespace Drupal\pingvin\Addon\Core\Auth;

use Drupal;
use Drupal\cordr\Route\Http\Response;
use Drupal\pingvin\Auth\JsonWebToken;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Logger\L;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Drupal\pingvin\Session\Session;
use Drupal\pingvin\User\User;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id = 'pingvin:auth_logout'
 * name = 'Pingvin Auth - Logout'
 * method = 'GET'
 * description = 'Logout route for Pingvin Auth.'
 * path = 'auth/logout'
 * permissions = [
 *  'access content'
 * ]
 * roles = [
 *  'authenticated'
 * ]
 * @route-end
 */
class Logout implements RouteInterface {
  /**
   * Handles Logout requests.
   *
   * @param Request $request
   *    The HTTP request object from Symfony.
   * @return ServerJsonResponse
   *    The JSON response containing the logout data.
   * @throws Exception
   *    Only if the middleware specifications are incorrect.
   */
  public function get(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['auth:jwt', 'body:json', 'client:cors', 'request']);
    if ($request instanceof ServerJsonResponse) return $request;

    /** @var array $context */
    $context = $request->attributes->get('context');
    /** @var ?Session $userSession */
    $userSession = $context['userSession'];
    /** @var string $userAgent */
    $userAgent = $context['userAgent'];

    if (!$userSession) {
      return new ServerJsonResponse([
        'message' => 'How did you even get here?',
      ], 400);
    }

    $accessToken = $userSession->getAccessToken();

    // we remove all the refresh tokens that are associated with this access token
    if (!Session::invalidateRefreshTokens($accessToken)) {
      return new ServerJsonResponse([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    // we remove the access token
    if (!Session::delete($accessToken)) {
      return new ServerJsonResponse([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    $tokenPayload = JsonWebToken::getPayload($accessToken);
    L::log('User @userId has logged out from @userAgent', [
      '@userId' => $tokenPayload['data']['userId'],
      '@userAgent' => $userAgent,
    ], 'info');

    // everything ok, we return a success message
    return new ServerJsonResponse([
      'message' => 'Logout successful.',
    ], 200);
  }
}
