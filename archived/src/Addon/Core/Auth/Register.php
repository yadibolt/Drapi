<?php

namespace Drupal\pingvin\Addon\Core\Auth;

use Drupal;
use Drupal\pingvin\Asserter\PasswordAsserter;
use Drupal\pingvin\Asserter\UsernameAsserter;
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
 * id = 'pingvin:auth_register'
 * name = 'Pingvin Auth - Register'
 * method = 'POST'
 * description = 'Register route for Pingvin Auth.'
 * path = 'auth/register'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * @route-end
 */
class Register implements RouteInterface {
  /**
   * Handles Register requests.
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

    // username is not required
    // if it's not provided, we will generate one
    // based on the email
    if (isset($data['username'])) {
      if (!UsernameAsserter::assert($data['username'])) {
        return new ServerJsonResponse([
          'message' => 'Username does not meet the requirements.',
        ], 400);
      }
    } else {
      $prefix = substr(explode('@', $data['mail'])[0], 0, 24);
      $data['username'] = $prefix . '_mail_reg_' . bin2hex(random_bytes(3));
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

    if (empty($data['password_confirm'])) {
      return new ServerJsonResponse([
        'message' => 'No password confirmation provided.',
      ], 400);
    }

    if ((string)$data['password'] !== (string)$data['password_confirm']) {
      return new ServerJsonResponse([
        'message' => 'Password and password confirmation do not match.',
      ], 400);
    }

    if (empty($data['langcode'])) {
      return new ServerJsonResponse([
        'message' => 'No langcode provided.',
      ], 400);
    }

    if (User::retrieveByPropertyMethod($data['mail'], 'mail')) {
      return new ServerJsonResponse([
        'message' => 'Email is already in use.',
      ], 400);
    }

    if (User::retrieveByPropertyMethod($data['username'], 'name')) {
      return new ServerJsonResponse([
        'message' => 'Username is already in use.',
      ], 400);
    }

    $user = \Drupal\user\Entity\User::create([
      'name' => $data['username'],
      'mail' => $data['mail'],
      'status' => 1,
      'langcode' => $data['langcode'],
      'init' => $data['mail'],
    ]);

    $user->setPassword($data['password']);

    try {
      $user->save();
    } catch (Exception) {
      return new ServerJsonResponse([
        'message' => 'Could not create user.',
      ], 400);
    }

    L::log('User @name has been registered.', [
      '@name' => $user->getAccountName(),
    ], 'info');

    $siteMail = Drupal::config('system.site')->get('mail');
    $mailClient = new MailClient('user_registration_mail', []);
    $mailClient->sendMail($siteMail, $user->getEmail(), 'Thank you for your registration', $user->getPreferredLangcode());

    return new ServerJsonResponse([
      'message' => 'User registered successfully.',
    ], 201);
  }
}
