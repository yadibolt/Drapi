<?php

namespace Drupal\drift_eleven\Routes\Auth;

use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandler;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drift_eleven\Core\Session\Session;
use Drupal\drift_eleven\Core\Session\Subject;
use Drupal\drift_eleven\Core\Utility\Enum\LoggerIntent;
use Drupal\drift_eleven\Core\Utility\Logger;

#[RouteHandler(
  id: 'auth:logout',
  name: '(Core) Logout Route',
  method: 'GET',
  path: 'auth/logout',
  description: 'Route for user logout',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: false
)]
class LogoutRoute extends RouteHandlerBase {
  public function handle(): Reply {
    /** @var string|null $token */
    $token = $this->context['token'] ?? null;
    /** @var Subject|null $user */
    $user = $this->context['user'] ?? null;

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
