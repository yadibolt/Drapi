<?php

namespace Drupal\drift_eleven\Routes\User;

use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandler;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drift_eleven\Core\Session\Subject;

#[RouteHandler(
  id: 'api:user',
  name: '(Core) User Route',
  method: 'GET',
  path: 'api/user',
  description: 'Route for user',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: false
)]
class UserRoute extends RouteHandlerBase {
  public function handle(): Reply {
    $ctx = $this->getMiddlewareContext();
    /** @var Subject|null $user */
    $user = $ctx['user'] ?? null;

    if (!$user) return Reply::make([
      'message' => 'Unauthorized.',
    ], 401);

    return Reply::make([
      'message' => 'Success.',
      'user' => [
        'id' => $user->getId(),
        'active' => $user->isActive(),
        'langcode' => $user->getLangcode(),
        'permissions' => $user->getPermissions(),
        'roles' => $user->getRoles(),
      ],
    ]);
  }
}
