<?php

namespace Drupal\pingvin\Addon\Core\Auth\Token;

use Drupal\pingvin\Auth\JsonWebToken;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Logger\L;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Drupal\pingvin\Session\Session;
use Drupal\user\Entity\User;
use Exception;
use Symfony\Component\HttpFoundation\Request;


/**
 * @route
 * id = 'pingvin:auth_token_refresh'
 * name = 'Pingvin Auth - Refresh Token'
 * method = 'GET'
 * description = 'Refresh token route for Pingvin Auth.'
 * path = 'auth/refresh-token'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * @route-end
 */
class RefreshToken implements RouteInterface {
  /**
   * Handles Token Refresh requests.
   *
   * @param Request $request
   *    The HTTP request object from Symfony.
   * @return ServerJsonResponse
   *    The JSON response containing the login data.
   * @throws Exception
   *    Only if the middleware specifications are incorrect.
   */
  public function get(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['auth:jwt-refresh', 'client:cors', 'request']);
    if ($request instanceof ServerJsonResponse) return $request;

    /** @var array $context */
    $context = $request->attributes->get('context');
    /** @var User $user */
    $user = $context['user'];
    /** @var ?Session $userSession */
    $userSession = $context['userSession'];
    /** @var string $userAgent */
    $userAgent = $context['userAgent'];

    if (!$userSession) {
      return new ServerJsonResponse([
        'message' => 'How did you even get here?',
      ], 400);
    }

    // we invalidate all access tokens for the user session
    $refreshToken = $userSession->getRefreshToken();
    Session::invalidateAccessTokens($refreshToken);

    // we create a new access token for the user
    $jwt = new JsonWebToken();
    $payload = [
      'userId' => $user->id(),
    ];
    $accessToken = $jwt->create(JsonWebToken::TOKEN_TYPE_ACCESS, $payload, JsonWebToken::BASIC);

    $session = new Session($user->id(), $accessToken, $refreshToken, $userAgent);
    try {
      $session->saveAccessToken();
    } catch (Exception) {
      return new ServerJsonResponse([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    L::log('User @userId generated new access token using @userAgent.', [
      '@userId' => $user->id(),
      '@userAgent' => $userAgent,
    ], 'info');

    return new ServerJsonResponse([
      'message' => 'Token generated successfully.',
      'accessToken' => $accessToken,
    ], 201);
  }
}
