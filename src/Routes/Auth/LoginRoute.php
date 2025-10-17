<?php

namespace Drupal\drift_eleven\Routes\Auth;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Password\PasswordInterface;
use Drupal\drift_eleven\Core\Auth\Enum\JWTIntent;
use Drupal\drift_eleven\Core\Auth\JWT;
use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandler;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drift_eleven\Core\Session\Enum\SubjectIntent;
use Drupal\drift_eleven\Core\Session\Session;
use Drupal\drift_eleven\Core\Utility\Enum\LoggerIntent;
use Drupal\drift_eleven\Core\Utility\Logger;
use Drupal\user\UserInterface;

#[RouteHandler(
  id: 'auth:login',
  name: '(Core) Login Route',
  method: 'POST',
  path: 'auth/login',
  description: 'Route for user login',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: false
)]
class LoginRoute extends RouteHandlerBase {
  public function handle(): Reply {
    $data = $this->getRequestData();
    if (empty($data['login'])) return Reply::make([
      'message' => 'Required parameter "login" is missing.',
    ], 400);

    if (empty($data['password'])) return Reply::make([
      'message' => 'Required parameter "password" is missing.',
    ], 400);

    $loadProp = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'mail' : 'name';
    $user = null; try {
      /** @var UserInterface[] $user */
      $user = Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadByProperties([$loadProp => $data['login']]);
      $user = $user ? reset($user) : null;
    } catch (InvalidPluginDefinitionException|PluginNotFoundException) {
      return Reply::make([
        'message' => 'Server error.',
      ], 500);
    }

    if (!$user) return Reply::make([
      'message' => 'No user found.',
    ], 404);

    /** @var PasswordInterface $passwordService */
    $passwordService = Drupal::service('password');
    if (!$passwordService->check($data['password'], $user->getPassword())) {
      return Reply::make([
        'message' => 'Invalid credentials provided.',
      ], 401);
    }

    if (!$user->isActive()) {
      return Reply::make([
        'message' => 'Unauthorized access.',
      ], 403);
    }

    $token = JWT::make(JWTIntent::ACCESS_TOKEN, [
      'user_id' => $user->id(),
      'type' => SubjectIntent::AUTHENTICATED,
    ]);

    $session = Session::make($token, $this->userAgent, $this->clientIp)->create();
    if (!$session) return Reply::make([
      'message' => 'Server error.',
    ], 500);

    Logger::l(
      channel: 'authentication',
      level: LoggerIntent::INFO,
      message: 'User with id @userId started a session using @userAgent agent.',
      context: [
        '@userId' => $user->id(),
        '@userAgent' => $this->userAgent,
      ]
    );

    return Reply::make([
      'message' => 'Success.',
      'token' => $token
    ]);
  }
}
