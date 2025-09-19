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
 * id= 'drift_eleven:user:forgot_password_confirm'
 * name= 'Drift Eleven - Forgot Password Confirm Route'
 * method= 'POST'
 * description= 'Forgot Password route for Drift Eleven.'
 * path= 'auth/forgot-password/confirm'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_anonym', 'request']
 * @route-end
 */
class ForgotPasswordConfirmRoute extends RouteFoundation {
  public function handle(): Reply {
    if (empty($this->data['token'])) {
      return new Reply([
        'message' => 'No token provided.',
      ], 400);
    }

    if (empty($this->data['password'])) {
      return new Reply([
        'message' => 'No password provided.',
      ], 400);
    }

    if (empty($this->data['password_confirm'])) {
      return new Reply([
        'message' => 'No confirm password provided.',
      ], 400);
    }

    $passwordAsserter = new UserAsserter();
    if ($passwordAsserter->assertPasswordRequirements($this->data['password'])) {
      return new Reply([
        'message' => 'Password does not meet the requirements.',
      ], 400);
    }

    if (!$passwordAsserter->assertPasswordMatch($this->data['password'], $this->data['password_confirm'])) {
      return new Reply([
        'message' => 'Password and confirm password do not match.',
      ], 400);
    }

    $userMail = SessionUser::fromResetToken($this->data['token']);
    $user = null;
    try {
      $user = Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadByProperties(['mail' => $userMail]);

      /** @var User $user */
      if ($user) $user = reset($user);
    } catch (Exception) {}

    if (!$user) {
      return new Reply([
        'message' => 'Invalid or expired token.',
      ], 400);
    }

    try {
      $user->setPassword($this->data['password']);
      $user->save();
    } catch (Exception) {
      return new Reply([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    $mailClient = new MailClient('user_forgot_password_confirmation_mail', []);
    $mailClient->sendMail(
      Drupal::config('system.site')->get('mail'), // TODO: make this configurable
      $user->getEmail(),
      'Password has been successfully changed',
      $user->getPreferredLangcode()
    );

    Logger::l('Password reset successful for user ID @userId.', [
      '@userId' => $user->id(),
    ], LoggerInterface::LEVEL_INFO);

    return new Reply([
      'message' => 'Password has been reset successfully.',
    ], 200);
  }
}
