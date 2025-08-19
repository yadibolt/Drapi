<?php

namespace Drupal\pingvin\Middleware\Auth;

use Drupal\pingvin\Auth\JsonWebToken;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Session\Session;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class AuthRefreshMiddleware {
  /**
   * The current request object.
   *
   * @var Request
   */
  protected Request $request;
  /**
   * The route definition array.
   *
   * This array contains the route information.
   * @see \Drupal\pingvin\Parser\RouteDocCommentParser
   * @var array
   */
  protected array $routeDefinition;
  /**
   * The access token extracted from the Authorization header.
   *
   * @var string
   */
  protected string $accessToken;
  /**
   * The refresh token extracted from the Authorization header.
   *
   * @var string
   */
  protected string $refreshToken;

  /**
   * Constructs the AuthRefreshMiddleware.
   *
   *
   * @param Request $request
   *    The current request object.
   * @param array $routeDefinition
   *    The route definition array.
   */
  public function __construct(Request $request, array $routeDefinition = []) {
    $this->request = $request;
    $this->routeDefinition = $routeDefinition;
  }

  /**
   * Applies the middleware to the request.
   * Middleware should be called at the very beginning of the request lifecycle.
   *
   * @return array|ServerJsonResponse
   *    Returns the attributes or a JSON response in case of an error.
   * @throws Exception
   */
  public function apply(): array|ServerJsonResponse {
    $jwt = new JsonWebToken();

    $authorizationHeader = $this->request->headers->get('authorization');
    // if the authorization header does not have 'Bearer' prefix or is not present,
    // we return a JSON response with an error message.
    if (!$authorizationHeader || !preg_match('/^Bearer\s+(\S+)$/', $authorizationHeader, $matches)) {
      return new ServerJsonResponse([
        'message' => 'Authorization header missing or invalid.',
        'actionId' => 'auth_header:invalid',
      ], 400);
    }
    // each route's request that implements this middleware has to have
    // a refreshToken.
    $this->refreshToken = $matches[1];
    if (empty($this->refreshToken)) {
      return new ServerJsonResponse([
        'message' => 'Refresh token is missing or is in invalid format.',
        'actionId' => 'refresh_token:invalid_format',
      ], 400);
    }
    // we try to verify the refresh token
    $tokenOk = $jwt->verify($this->refreshToken);
    if ($tokenOk['actionId'] === 'refresh_token:invalid_format') {
      return new ServerJsonResponse([
        'message' => 'Refresh token is in invalid format.',
        'actionId' => $tokenOk['actionId'],
      ], 400);
    }

    if ($tokenOk['actionId'] === 'refresh_token:invalid') {
      return new ServerJsonResponse([
        'message' => 'Refresh token invalid.',
        'actionId' => $tokenOk['actionId'],
      ], 401);
    }

    if ($tokenOk['actionId'] === 'refresh_token:expired') {
      return new ServerJsonResponse([
        'message' => 'Refresh token expired.',
        'actionId' => $tokenOk['actionId'],
      ], 401);
    }

    // if the session does not exist, we return a response with 'session:invalid'.
    $session = Session::retrieve(
      token: $this->refreshToken,
      tokenType: JsonWebToken::JWT_TOKEN_TYPE_REFRESH
    );

    if (empty($session)) {
      return new ServerJsonResponse([
        'message' => 'Session does not exist.',
        'actionId' => 'session:invalid',
      ], 401);
    }

    // the session exists, which means that refresh token is valid
    // we can now retrieve the refresh token as additional context
    return [
      'refreshToken' => $this->refreshToken,
    ];
  }
}
