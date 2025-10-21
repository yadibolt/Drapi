<?php

namespace Drupal\drapi\Routes\v1\Auth\Password;

use Drupal;
use Drupal\drapi\Core\Http\Mail\MailClient;
use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Base\RouteHandler;
use Drupal\drapi\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drapi\Core\Session\Subject;

#[RouteHandler(
  id: 'auth:password:reset',
  name: '[DrapiCore] Reset Password Route',
  method: 'POST',
  path: 'v1/auth/password/reset',
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

    MailClient::make(
      moduleName: MODULE_NAME_DEFAULT,
      from: $systemMail,
      to: $data['mail'],
      subject: 'Here is the link to reset your password',
      themeKey: 'user_password_reset_mail',
      themeParams: [
        'token' => $token,
      ]
    )->sendMail();

    return Reply::make([
      'message' => 'If the mail exists, the mail has been sent.',
    ]);
  }
}
