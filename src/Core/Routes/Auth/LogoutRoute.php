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
 * id= 'drift_eleven:auth:logout'
 * name= 'Drift Eleven - Logout Route'
 * method= 'GET'
 * description= 'Logout route for Drift Eleven.'
 * path= 'auth/logout'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth', 'request']
 * @route-end
 */
class LogoutRoute extends RouteFoundation {
  public function handle(): Reply {
    if (!Session::invalidate($this->context['accessToken'])) return new Reply([
      'message' => 'Logout failed.',
    ], 500);

    Logger::l('User with id @userId ended a session using @userAgent.', [
      '@userId' => $this->context['user']['id'],
      '@userAgent' => $this->userAgent,
    ], LoggerInterface::LEVEL_INFO);

    return new Reply([
      'message' => 'Logout successful.',
    ], 200);
  }
}
