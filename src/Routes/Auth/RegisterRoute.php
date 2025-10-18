<?php

namespace Drupal\drapi\Routes\Auth;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\drapi\Core\Http\Mail\MailClient;
use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Base\RouteHandler;
use Drupal\drapi\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drapi\Core\Utility\Enum\LoggerIntent;
use Drupal\drapi\Core\Utility\Logger;
use Drupal\drapi\Core\Utility\Trait\AssertionTrait;
use Drupal\drapi\Core\Utility\Trait\StringGeneratorTrait;
use Drupal\user\Entity\User;

#[RouteHandler(
  id: 'auth:register',
  name: '[DrapiCore] Register Route',
  method: 'POST',
  path: 'auth/register',
  description: 'Route for user registration',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: false
)]
class RegisterRoute extends RouteHandlerBase {
  use AssertionTrait;
  use StringGeneratorTrait;

  public function handle(): Reply {
    $systemMail = Drupal::config('system.site')->get('mail');
    $data = $this->getRequestData();
    if (empty($data['mail'])) return Reply::make([
      'message' => 'Required parameter "mail" is missing.',
    ], 400);

    if (empty($data['password'])) return Reply::make([
      'message' => 'Required parameter "password" is missing.',
    ], 400);

    if (empty($data['password_confirm'])) return Reply::make([
      'message' => 'Required parameter "password_confirm" is missing.',
    ], 400);

    $username = $data['username'] ?? $this->generateUsernameFromMail($data['mail']);
    if (!$this->assertionForUsername($username)) return Reply::make([
      'message' => 'Username does not meet the requirements.',
    ], 400);

    if (!$this->assertionForPassword($data['password'])) return Reply::make([
      'message' => 'Password does not meet the requirements.',
    ], 400);

    if ($data['password'] !== $data['password_confirm']) return Reply::make([
      'message' => 'Password and password confirmation do not match.',
    ], 400);

    $usage = 0; try {
      $user = Drupal::entityTypeManager()
        ->getStorage('user');
      if ($user->loadByProperties(['name' => $username])) $usage++;
      if ($user->loadByProperties(['mail' => $data['mail']])) $usage += 2;
    } catch (InvalidPluginDefinitionException|PluginNotFoundException) {
      return Reply::make([
        'message' => 'Server error.',
      ], 500);
    }

    $response = match ($usage) {
      0 => null,
      1 => Reply::make([
        'message' => 'Username is already taken.',
      ], 409),
      2 => Reply::make([
        'message' => 'Email is already registered.',
      ], 409),
      3 => Reply::make([
        'message' => 'Username and email are already taken.',
      ], 409),
      default => Reply::make([
        'message' => 'Server error.',
      ], 500),
    };

    if ($response instanceof Reply) return $response;

    $user = User::create([
      'name' => $username,
      'mail' => $data['mail'],
      'status' => 1,
      'langcode' => $this->getRequestLangcode(),
      'init' => $data['mail'],
    ]);

    $user->setPassword($data['password']);

    try {
      $user->save();
    } catch (EntityStorageException) {
      return Reply::make([
        'message' => 'Server error.',
      ], 500);
    }

    MailClient::make(
      moduleName: MODULE_NAME_DEFAULT,
      from: $systemMail,
      to: $user->getEmail(),
      subject: 'Thank you for registering!',
      themeKey: 'user_registration_mail',
    )->sendMail();

    Logger::l(
      channel: 'registration',
      level: LoggerIntent::INFO,
      message: 'New user registered with id @userId.',
      context: [
        '@userId' => $user->id(),
      ]
    );

    return Reply::make([
      'message' => 'Success.',
    ]);
  }
}
