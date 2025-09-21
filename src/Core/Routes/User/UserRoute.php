<?php

namespace Drupal\drift_eleven\Core\Routes\User;

use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\user\Entity\User;

/**
 * @route
 * id= 'drift_eleven:user:user'
 * name= 'Drift Eleven - User Route'
 * method= 'GET'
 * description= 'Get user information'
 * path= 'api/user'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth', 'request']
 * useCache= false
 * @route-end
 */
class UserRoute extends RouteFoundation {
  public function handle(): Reply {
    $userId = $this->context['user']['id'] ?: null;
    if (!$userId) {
      return new Reply([
        'message' => 'User not found',
      ], 404);
    }

    $user = User::load($userId);
    if (!$user) {
      return new Reply([
        'message' => 'User not found',
      ], 404);
    }

    return new Reply([
      'id' => $user->id(),
      'name' => $user->getAccountName(),
      'email' => $user->getEmail(),
      'createdAt' => $user->getCreatedTime(),
      'changedAt' => $user->getChangedTime(),
      'active' => $user->isActive(),
    ], 200);
  }
}
