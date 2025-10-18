<?php

namespace Drupal\drift_eleven\Routes\Auth\Password;

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
use Drupal\drift_eleven\Core\Session\Subject;
use Drupal\drift_eleven\Core\Utility\Enum\LoggerIntent;
use Drupal\drift_eleven\Core\Utility\Logger;
use Drupal\user\UserInterface;

#[RouteHandler(
  id: 'auth:password:reset',
  name: '(Core) Reset Password Route',
  method: 'POST',
  path: 'auth/password/reset',
  description: 'Route for user password reset.',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: false
)]
class ResetRoute extends RouteHandlerBase {
  public function handle(): Reply {
    $systemMail = Drupal::config('system.site')->get('mail');
    $data = $this->getRequestData();

    if (empty($data['mail'])) return Reply::make([
      'message' => 'Required field "mail" is missing.',
    ], 400);

    if (!filter_var($data['mail'], FILTER_VALIDATE_EMAIL)) return Reply::make([
      'message' => 'Mail format is not valid.',
    ], 400);

    $token = Subject::generateForgotPasswordToken(
      $data['mail'],
      $this->getRequestLangcode()
    );

    if ($token) {
      if (!Subject::insertForgotPasswordToken($token)) return Reply::make([
        'message' => 'Server error.',
      ], 500);
    }

    // TODO send mail

    return Reply::make([
      'message' => 'If the mail exists, the mail has been sent.',
    ]);
  }
}
