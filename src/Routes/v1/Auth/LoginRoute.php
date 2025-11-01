<?php

namespace Drupal\drapi\Routes\v1\Auth;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Password\PasswordInterface;
use Drupal\drapi\Core\Auth\Enum\JWTIntent;
use Drupal\drapi\Core\Auth\JWT;
use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Base\RouteHandler;
use Drupal\drapi\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drapi\Core\Session\Enum\SubjectIntent;
use Drupal\drapi\Core\Session\Session;
use Drupal\drapi\Core\Utility\Enum\LoggerIntent;
use Drupal\drapi\Core\Utility\Logger;
use Drupal\user\UserInterface;

#[RouteHandler(
  id: 'auth:login',
  name: '[DrapiCore] Login Route',
  method: 'POST',
  path: 'v1/auth/login',
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
      'username' => $user->getAccountName(),
      'type' => SubjectIntent::AUTHENTICATED,
      'langcode' => $user->getPreferredLangcode(),
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
