<?php

namespace Drupal\pingvin\Addon\Core\Auth;

use Drupal;
use Drupal\cordr\Route\Http\Response;
use Drupal\pingvin\Auth\JsonWebToken;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Drupal\pingvin\Session\Session;
use Drupal\pingvin\User\User;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    /** @var array $data */
    $data = $context['data'];
    /** @var string $userAgent */
    $userAgent = $context['userAgent'];

    if (empty($data['login'])) {
      return new ServerJsonResponse([
        'message' => 'No login provided.',
      ], 400);
    }

    if (empty($data['password'])) {
      return new ServerJsonResponse([
        'message' => 'No password provided.',
      ], 400);
    }

    // we want to allow login with email or username
    // TODO: this might be a configuration option in the future
    $propertyMethod = !filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'name' : 'mail';
    $user = User::retrieveByPropertyMethod($data['login'], $propertyMethod);

    if (!$user) {
      return new ServerJsonResponse([
        'message' => 'Invalid login or password.',
      ], 401);
    }

    if (!Drupal::service('password')->check($data['password'], $user->getPassword())) {
      return new ServerJsonResponse([
        'message' => 'Invalid login or password.',
      ], 401);
    }

    // user has the right credentials, we now create a new session with JWT
    // this session contains very minimal information exposure due to security reasons
    $jwt = new JsonWebToken();
    $payload = [
      'user_id' => $user->id(),
    ];

    $accessToken = $jwt->create(JsonWebToken::JWT_TOKEN_TYPE_ACCESS, $payload);
    $refreshToken = $jwt->create(JsonWebToken::JWT_TOKEN_TYPE_REFRESH);

    $session = new Session($user->id(), $accessToken, $refreshToken, $userAgent);
    if (!$session->save()) {
      return new ServerJsonResponse([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    return new ServerJsonResponse([
      'message' => 'Login successful.',
      'accessToken' => $accessToken,
      'refreshToken' => $refreshToken,
    ], 200);
  }
}
