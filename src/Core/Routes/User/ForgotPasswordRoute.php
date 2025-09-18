<?php

namespace Drupal\drift_eleven\Core\Routes\User;

use Drupal;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Logger\Logger;
use Drupal\drift_eleven\Core\Logger\LoggerInterface;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\drift_eleven\Core\Session\SessionUser;
use Drupal\drift_eleven\Core\SimpleMailClient\MailClient;
use Drupal\user\Entity\User;
use Exception;

/**
 * @route
 * id= 'drift_eleven:user:forgot_password'
 * name= 'Drift Eleven - Forgot Password Route'
 * method= 'POST'
 * description= 'Forgot Password route for Drift Eleven.'
 * path= 'auth/forgot-password'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_anonym', 'request']
 * @route-end
 */
class ForgotPasswordRoute extends RouteFoundation {
  public function handle(): Reply {
    if (empty($this->data['mail'])) {
      return new Reply([
        'message' => 'No mail provided.',
      ], 400);
    }

    if (!filter_var($this->data['mail'], FILTER_VALIDATE_EMAIL)) {
      return new Reply([
        'message' => 'Invalid email format.',
      ], 400);
    }

    $token = null;
    try {
      $token = SessionUser::makeResetToken($this->data['mail'], $this->context['request']['langcode'] ?: 'en');
    } catch (Exception) {
      return new Reply([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    if (!SessionUser::insertResetToken($this->data['mail'], $token)) {
      return new Reply([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    $mailClient = new MailClient('user_forgot_password_mail', [
      'token' => $token,
    ]);
    $mailClient->sendMail(
      Drupal::config('system.site')->get('mail'), // TODO: make this configurable
      $this->data['mail'],
      'You have requested a password reset',
      $this->context['request']['langcode'] ?: 'en'
    );

    Logger::l('Password reset requested for email @mail.', [
      '@mail' => $this->data['mail'],
    ], LoggerInterface::LEVEL_INFO);

    return new Reply([
      'message' => 'If an account with that email exists, a password reset link has been sent.',
    ], 200);
  }
}
