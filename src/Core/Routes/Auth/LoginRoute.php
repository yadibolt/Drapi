<?php

namespace Drupal\drift_eleven\Core\Routes\Auth;

use Drupal;
use Drupal\drift_eleven\Core\Auth\JsonWebToken;
use Drupal\drift_eleven\Core\Auth\JsonWebTokenInterface;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Logger\Logger;
use Drupal\drift_eleven\Core\Logger\LoggerInterface;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\drift_eleven\Core\Session\Session;
use Drupal\user\Entity\User;
use Exception;

/**
 * @route
 * id= 'drift_eleven:auth:login'
 * name= 'Drift Eleven - Login Route'
 * method= 'POST'
 * description= 'An Example Drift Eleven Route'
 * path= 'auth/login'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_anonym', 'request']
 * @route-end
 */
class LoginRoute extends RouteFoundation {
  public function handle(): Reply {
    if (empty($this->data['login'])) return new Reply([
      'message' => 'No login provided.',
    ], 400);

    if (empty($this->data['password'])) return new Reply([
      'message' => 'No password provided.',
    ], 400);

    // TODO: Make this a configuration option.
    $property = filter_var($this->data['login'], FILTER_VALIDATE_EMAIL) ? 'mail' : 'name';

    $user = null;
    try {
      $user = Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadByProperties([$property => $this->data['login']]);

      /** @var User $user */
      if ($user) $user = reset($user);
    } catch (Exception) {}

    if (!$user) return new Reply([
      'message' => 'No user found or could not be retrieved.',
    ], 400);


    /** @var Drupal\Core\Password\PasswordInterface $passwordService */
    $passwordService = Drupal::service('password');
    if (!$passwordService->check($this->data['password'], $user->getPassword())) {
      return new Reply([
        'message' => 'Invalid login or password.',
      ], 401);
    }

    if ($user->isBlocked()) return new Reply([
      'message' => 'User is blocked.',
    ], 403);

    $jsonWebToken = new JsonWebToken();
    $accessTok = $jsonWebToken->make(JsonWebTokenInterface::TOKEN_ACCESS, [
      'userId' => $user->id(),
    ]);
    $refreshTok = $jsonWebToken->make(JsonWebTokenInterface::TOKEN_REFRESH, [],
      JsonWebTokenInterface::REFRESH_EXP_TIME_DEFAULT);

    // create refresh token session
    $refreshTokId = new Session($user->id(), $refreshTok, JsonWebTokenInterface::TOKEN_REFRESH,
    0, $this->userAgent, $this->hostname)->save();
    if (!$refreshTokId) return new Reply([
      'message' => 'Login failed.',
    ], 500);

    // create access token session
    $accessTokId = new Session($user->id(), $accessTok, JsonWebTokenInterface::TOKEN_ACCESS,
    $refreshTokId, $this->userAgent, $this->hostname)->save();

    if (!$accessTokId) return new Reply([
      'message' => 'Login failed.',
    ], 500);

    Logger::l('User @username (@userId) started a new session using @userAgent.', [
      '@username' => $user->getAccountName(),
      '@userId' => $user->id(),
      '@userAgent' => $this->userAgent,
    ], LoggerInterface::LEVEL_INFO);

    return new Reply([
      'message' => 'Login successful.',
      'accessToken' => $accessTok,
      'refreshToken' => $refreshTok,
    ], 200);
  }
}
