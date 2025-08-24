<?php

namespace Drupal\pingvin\Addon\Core\Auth\Token;

use Drupal\pingvin\Asserter\PasswordAsserter;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Mail\MailClient;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Drupal\pingvin\User\User;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * @route
 * id = 'pingvin:auth_forgot_password_confirm'
 * name = 'Pingvin Auth - Forgot Password Confirm'
 * method = 'POST'
 * description = 'Forgot Password Confirm route for Pingvin Auth.'
 * path = 'auth/forgot-password/confirm'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * @route-end
 */
class ForgotPasswordConfirm implements RouteInterface {
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

    if (empty($data['token'])) {
      return new ServerJsonResponse([
        'message' => 'No token provided.',
      ], 400);
    }

    if (empty($data['password'])) {
      return new ServerJsonResponse([
        'message' => 'No password provided.',
      ], 400);
    }

    if (!PasswordAsserter::assert($data['password'])) {
      return new ServerJsonResponse([
        'message' => 'Password does not meet the requirements.',
      ], 400);
    }

    $resetToken = User::verifyResetPasswordToken($data['token']);
    if (!$resetToken) {
      return new ServerJsonResponse([
        'message' => 'Invalid or expired token. Please request a new password reset.',
      ], 400);
    }

    $user = User::retrieveByPropertyMethod($resetToken->mail, 'mail');
    if (!$user) {
      return new ServerJsonResponse([
        'message' => 'User not found.',
      ], 404);
    }

    try {
      $user->setPassword($data['password']);
      $user->save();

      User::invalidatePasswordResetTokens($resetToken->mail);
    } catch (Exception) {
      return new ServerJsonResponse([
        'message' => 'Server could not process the request.',
      ], 500);
    }

    return new ServerJsonResponse([
      'message' => 'Password has been reset successfully.',
    ], 200);
  }
}
