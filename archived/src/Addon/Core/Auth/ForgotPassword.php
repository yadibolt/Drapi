<?php

namespace Drupal\pingvin\Addon\Core\Auth;

use Drupal;
use Drupal\Core\Render\Element\File;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Logger\L;
use Drupal\pingvin\Mail\MailClient;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Drupal\pingvin\User\User;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id = 'pingvin:auth_forgot_password'
 * name = 'Pingvin Auth - Forgot Password'
 * method = 'POST'
 * description = 'Forgot Password route for Pingvin Auth.'
 * path = 'auth/forgot-password'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * @route-end
 */
class ForgotPassword implements RouteInterface {
  /**
   * Handles Login requests.
   *
   * @param Request $request
   *    The HTTP request object from Symfony.
   * @return ServerJsonResponse
   *    The JSON response containing the login data.
   * @throws Exception
   *    Only if the middleware specifications are incorrect.
   */
  public function post(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['auth:jwt', 'body:json', 'client:cors', 'request']);
    if ($request instanceof ServerJsonResponse) return $request;

    /** @var array $context */
    $context = $request->attributes->get('context');
    /** @var array $data */
    $data = $context['data'];

    if (empty($data['mail'])) {
      return new ServerJsonResponse([
        'message' => 'No mail provided.',
      ], 400);
    }

    if (!filter_var($data['mail'], FILTER_VALIDATE_EMAIL)) {
      return new ServerJsonResponse([
        'message' => 'Invalid email format.',
      ], 400);
    }

    try {
      $token = User::createResetPasswordToken($data['mail']);

      if (!$token) {
        return new ServerJsonResponse([
          'message' => 'If an account with that email exists, a password reset link has been sent.',
        ], 200);
      }

      if (!User::insertPasswordResetToken($data['mail'], $token)) {
        return new ServerJsonResponse([
          'message' => 'Could not create password reset token.',
        ], 500);
      }

      // all ok, we send the mail with reset token
      $siteMail = \Drupal::config('system.site')->get('mail');
      $mailClient = new MailClient('user_forgot_password_mail', ['token' => $token,]);
      $langcode = explode('-', $token)[0] ?: 'en';

      $randomFile = \Drupal\file\Entity\File::load(5);
      $uri = $randomFile->getFileUri();
      $filePath = Drupal::service('file_system')->realpath($uri);
      $fileName = $randomFile->getFilename();

      $mailClient->sendMailWithAttachments($siteMail, $data['mail'], 'Password Reset Request', [
        [
          'fileName' => $fileName,
          'filePath' => $filePath,
        ]
      ], $langcode);
    } catch (Exception $e) {
      return new ServerJsonResponse([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    L::log('New password request requested for @mail.', [
      '@mail' => $data['mail'],
    ], 'info');

    return new ServerJsonResponse([
      'message' => 'If an account with that email exists, a password reset link has been sent.',
    ], 200);
  }
}
