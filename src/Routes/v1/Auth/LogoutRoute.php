<?php

namespace Drupal\drapi\Routes\v1\Auth;

use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Base\RouteHandler;
use Drupal\drapi\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drapi\Core\Session\Session;
use Drupal\drapi\Core\Session\Subject;
use Drupal\drapi\Core\Utility\Enum\LoggerIntent;
use Drupal\drapi\Core\Utility\Logger;

#[RouteHandler(
  id: 'auth:logout',
  name: '[DrapiCore] Logout Route',
  method: 'GET',
  path: 'v1/auth/logout',
  description: 'Route for user logout',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: false
)]
class LogoutRoute extends RouteHandlerBase {
  public function handle(): Reply {
    $ctx = $this->getMiddlewareContext();
    /** @var string|null $token */
    $token = $ctx['token'] ?? null;
    /** @var Subject|null $user */
    $user = $ctx['user'] ?? null;

    if (!$token) return Reply::make([
      'message' => 'No token provided.',
    ], 400);

    Session::make($token)?->delete();

    Logger::l(
      channel: 'authentication',
      level: LoggerIntent::INFO,
      message: 'User with id @userId logged out.',
      context: [
        '@userId' => $user->getId(),
      ]
    );

    return Reply::make([
      'message' => 'Success.',
    ]);
  }
}
