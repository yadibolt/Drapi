<?php

namespace Drupal\pingvin\Middleware\Auth;

use Drupal\pingvin\Auth\JsonWebToken;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Session\Session;
use Drupal\user\Entity\User;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class AuthMiddleware {
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
   * Constructs the AuthMiddleware.
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
    // an accessToken.
    $this->accessToken = $matches[1];
    if (empty($this->accessToken)) {
      return new ServerJsonResponse([
        'message' => 'Access token is missing or is in invalid format.',
        'actionId' => 'token:invalid_format',
      ], 400);
    }
    // we try to verify the access token
    $tokenOk = $jwt->verify($this->accessToken);
    if ($tokenOk['actionId'] === 'token:invalid_format') {
      return new ServerJsonResponse([
        'message' => 'Access token is in invalid format.',
        'actionId' => $tokenOk['actionId'],
      ], 400);
    }

    if ($tokenOk['actionId'] === 'token:invalid') {
      return new ServerJsonResponse([
        'message' => 'Access token invalid.',
        'actionId' => $tokenOk['actionId'],
      ], 401);
    }

    if ($tokenOk['actionId'] === 'token:expired') {
      return new ServerJsonResponse([
        'message' => 'Access token expired.',
        'actionId' => $tokenOk['actionId'],
      ], 401);
    }

    // if the session does not exist, we return a response with 'session:invalid'
    // the client side application has to handle this response and call
    // /token/refresh endpoint to get a new access token.
    // if the session is not refreshed, all actions are forbidden
    $session = Session::retrieve(
      token: $this->accessToken,
      tokenType: JsonWebToken::JWT_TOKEN_TYPE_ACCESS
    );

    if (empty($session)) {
      return new ServerJsonResponse([
        'message' => 'Session does not exist.',
        'actionId' => 'session:invalid',
      ], 401);
    }

    // we try to load actual user from the session retrieval, not from the token
    // just to be sure that the session contains the entityId
    if (!is_object($session) || empty($session->getEntityId())) {
      return new ServerJsonResponse([
        'message' => 'Not an object or invalid entity.',
        'actionId' => 'session:invalid_format',
      ], 500);
    }

    $user = User::load((int)$session->getEntityId());
    if (!$user) {
      return new ServerJsonResponse([
        'message' => 'User not found.',
        'actionId' => 'user:not_found',
      ], 404);
    }

    if (!$user->isActive()) {
      return new ServerJsonResponse([
        'message' => 'User is not active.',
        'actionId' => 'user:not_active',
      ], 403);
    }

    // we also check the permissions and roles to access the route
    // that are defined in the Doc Comments of the route class
    // User has to have all the permissions specified by the route definition
    $requiredPermissions = $this->routeDefinition['permissions'] ?: [];
    if (array_any($requiredPermissions, fn($requiredPermission) => !$user->hasPermission($requiredPermission))) {
      return new ServerJsonResponse([
        'message' => 'User does not have the required permissions.',
        'actionId' => 'user:missing_permissions',
      ], 403);
    }

    $requiredRoles = $this->routeDefinition['roles'] ?: [];
    if (array_any($requiredRoles, fn($requiredRole) => !$user->hasRole($requiredRole))) {
      return new ServerJsonResponse([
        'message' => 'User does not have the required roles.',
        'actionId' => 'user:missing_roles',
      ], 403);
    }

    // everything is ok, we set the user as a context for the route
    // doing this we save some database calls for the route
    return [
      'user' => $user,
      'userSession' => $session,
    ];
  }
}
