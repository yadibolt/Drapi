<?php

namespace Drupal\drift_eleven\Routes\Auth\Password;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
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
use Drupal\drift_eleven\Core\Utility\Trait\AssertionTrait;
use Drupal\user\UserInterface;

#[RouteHandler(
  id: 'auth:password:confirm',
  name: '(Core) Reset Password Confirm Route',
  method: 'POST',
  path: 'auth/password/confirm',
  description: 'Route for user password reset confirmation.',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: false
)]
class ConfirmRoute extends RouteHandlerBase {
  use AssertionTrait;

  public function handle(): Reply {
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

    // TODO send mail

    return Reply::make([
      'message' => 'Success.',
    ]);
  }
}
