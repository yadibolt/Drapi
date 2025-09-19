<?php

namespace Drupal\drift_eleven\Core\Routes\User;

use Drupal;
use Drupal\drift_eleven\Core\Asserters\PasswordAsserter;
use Drupal\drift_eleven\Core\Asserters\UserAsserter;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Logger\Logger;
use Drupal\drift_eleven\Core\Logger\LoggerInterface;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\drift_eleven\Core\Session\SessionUser;
use Drupal\drift_eleven\Core\SimpleMailClient\MailClient;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\user\Entity\User;
use Exception;

/**
 * @route
 * id= 'drift_eleven:user:register'
 * name= 'Drift Eleven - Register Route'
 * method= 'POST'
 * description= 'Register route for Drift Eleven.'
 * path= 'auth/register'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_anonym', 'request']
 * @route-end
 */
class RegisterRoute extends RouteFoundation {
  public function handle(): Reply {
    if (empty($this->data['mail'])) {
      return new Reply([
        'message' => 'No mail provided.',
      ], 400);
    }

    $username = $this->data['username'] ?: null;
    if (empty($username)) {
      $prefix = substr(explode('@', $this->data['mail'])[0], 0, 24);
      $username = $prefix . '_mail_reg_' . bin2hex(random_bytes(3));
    }

    $userAsserter = new UserAsserter();
    if (!$userAsserter->assertUsernameRequirements($username)) {
      return new Reply([
        'message' => 'Username does not meet the requirements.',
      ], 400);
    }

    if (empty($this->data['password'])) {
      return new Reply([
        'message' => 'No password provided.',
      ], 400);
    }

    if (empty($this->data['password_confirm'])) {
      return new Reply([
        'message' => 'No password confirmation provided.',
      ], 400);
    }

    if (!$userAsserter->assertPasswordRequirements($this->data['password'])) {
      return new Reply([
        'message' => 'Password does not meet the requirements.',
      ], 400);
    }

    if (!$userAsserter->assertPasswordMatch($this->data['password'], $this->data['password_confirm'])) {
      return new Reply([
        'message' => 'Password and password confirmation do not match.',
      ], 400);
    }

    $langcode = 'en';
    if (!empty($this->data['langcode'])) {
      $langcode = $this->data['langcode'];
    }

    $inUse = ['mail' => true, 'username' => true];
    try {
      $user = Drupal::entityTypeManager()
        ->getStorage('user');
      $inUse['mail'] = (bool)$user->loadByProperties(['mail' => $this->data['mail']]);
      $inUse['username'] = (bool)$user->loadByProperties(['name' => $username]);
    } catch (Exception) {}

    if ($inUse['mail'] || $inUse['username']) {
      return new Reply([
        'message' => $inUse['mail'] ? 'Email is already in use.' : 'Username is already in use.',
      ], 400);
    }

    $user = User::create([
      'name' => $username,
      'mail' => $this->data['mail'],
      'status' => 1,
      'langcode' => $langcode,
      'init' => $this->data['mail'],
    ]);
    $user->setPassword($this->data['password']);

    try {
      $user->save();
    } catch (Exception) {
      return new Reply([
        'message' => 'Account could not be created.',
      ], 500);
    }

    $mailClient = new MailClient('user_registration_mail', []);
    $mailClient->sendMail(
      Drupal::config('system.site')->get('mail'), // TODO: make this configurable
      $user->getEmail(),
      'Thank you for your registration',
      $user->getPreferredLangcode()
    );

    Logger::l('New user registered with ID @userId and username @username.', [
      '@userId' => $user->id(),
      '@username' => $user->getAccountName(),
    ], LoggerInterface::LEVEL_INFO);

    return new Reply([
      'message' => 'User registered successfully.',
    ], 200);
  }
}
