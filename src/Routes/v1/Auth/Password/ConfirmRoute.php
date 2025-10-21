<?php

namespace Drupal\drapi\Routes\v1\Auth\Password;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\drapi\Core\Auth\JWT;
use Drupal\drapi\Core\Http\Mail\MailClient;
use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Base\RouteHandler;
use Drupal\drapi\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drapi\Core\Session\Subject;
use Drupal\drapi\Core\Utility\Trait\AssertionTrait;
use Drupal\user\UserInterface;

#[RouteHandler(
  id: 'auth:password:confirm',
  name: '[DrapiCore] Reset Password Confirm Route',
  method: 'POST',
  path: 'v1/auth/password/confirm',
  description: 'Route for user password reset confirmation.',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: false
)]
class ConfirmRoute extends RouteHandlerBase {
  use AssertionTrait;

  public function handle(): Reply {
    $systemMail = Drupal::config('system.site')->get('mail');
    $data = $this->getRequestData();
    $queryParams = $this->getQueryParams();

    if (empty($queryParams['token'])) return Reply::make([
      'message' => 'Required query parameter "token" is missing.',
    ]);

    if (empty($data['password'])) return Reply::make([
      'message' => 'Required parameter "password" is missing.',
    ], 400);

    if (empty($data['password_confirm'])) return Reply::make([
      'message' => 'Required parameter "password_confirm" is missing.',
    ], 400);

    if (!$this->assertionForPassword($data['password'])) return Reply::make([
      'message' => 'Password does not meet the requirements.',
    ], 400);

    if ($data['password'] !== $data['password_confirm']) return Reply::make([
      'message' => 'Password and password confirmation do not match.',
    ], 400);

    $record = Subject::forgotPasswordRecordExists($queryParams['token']);
    if (!$record) return Reply::make([
      'message' => 'Invalid or expired token.',
    ], 400);

    $payload = JWT::payloadFrom($queryParams['token']);
    if (empty($payload) ||
      !isset($payload['data']) ||
      !isset($payload['data']['user_id']) ||
      !isset($payload['data']['mail']) ||
      !isset($payload['data']['langcode'])) {
      return Reply::make([
        'message' => 'Server error.',
      ], 500);
    }

    $user = null; try {
      /** @var UserInterface[] $user */
      $user = Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadByProperties(['mail' => $payload['data']['mail']]);
      $user = $user ? reset($user) : null;
    } catch (InvalidPluginDefinitionException|PluginNotFoundException) {
      return Reply::make([
        'message' => 'Server error.',
      ], 500);
    }

    if (!$user) return Reply::make([
      'message' => 'No user found.',
    ], 404);

    try {
      $user->setPassword($data['password']);
      $user->save();

      if (!Subject::deleteForgotPasswordRecords($payload['data']['mail'])) return Reply::make([
        'message' => 'Server error.',
      ], 500);
    } catch (EntityStorageException) {
      return Reply::make([
        'message' => 'Server error.',
      ], 500);
    }

    MailClient::make(
      moduleName: MODULE_NAME_DEFAULT,
      from: $systemMail,
      to: $user->getEmail(),
      subject: 'Your password has been reset',
      themeKey: 'user_password_reset_confirmation_mail',
    )->sendMail();

    return Reply::make([
      'message' => 'Success.',
    ]);
  }
}
