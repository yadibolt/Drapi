<?php

namespace Drupal\drift_eleven\Core\Middleware\Auth;

use Drupal\drift_eleven\Core\Auth\JsonWebToken;
use Drupal\drift_eleven\Core\HTTP\Request\RequestAttributesTrait;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\HTTP\Response\ReplyInterface;
use Drupal\drift_eleven\Core\Middleware\MiddlewareInterface;
use Drupal\drift_eleven\Core\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class AuthMiddleware implements MiddlewareInterface {
  use RequestAttributesTrait;

  protected Request $request;
  protected array $route;

  public function __construct(Request $request, array $route = []) {
    $this->request = $request;
    $this->route = $route;
  }

  public function run(): ?Reply {
    $jsonWebToken = new JsonWebToken();

    $authHeader = $this->request->headers->get('authorization');
    if (!$authHeader || !preg_match('/^Bearer\s+(\S+)$/', $authHeader, $matches)) {
      return new Reply([
        'message' => 'Missing authorization header.',
        'actionId' => ReplyInterface::ACTION_INVALID_HEADER,
      ], 401);
    }

    $isOk = $jsonWebToken->validate($matches[1]);
    if (!$isOk->valid || $isOk->expired || $isOk->error) {
      if ($isOk->expired) Session::delete($matches[1]);

      return new Reply([
        'message' => 'Invalid token.',
        'actionId' => ReplyInterface::ACTION_INVALID_TOKEN . ';' . $isOk->action,
      ], 401);
    }

    $payload = $jsonWebToken->payloadFrom($matches[1]);
    if (empty($payload) || !isset($payload['data']['userId']) || !is_numeric($payload['data']['userId'])) {
      return new Reply([
        'message' => 'Invalid token payload.',
        'actionId' => ReplyInterface::ACTION_INVALID_PAYLOAD,
      ], 401);
    }

    if ((int)$payload['data']['userId'] <= 0) {
      return new Reply([
        'message' => 'Unauthorized access.',
        'actionId' => ReplyInterface::ACTION_UNAUTHORIZED_ACCESS,
      ], 403);
    }

    $sessionUser = Session::findUser($matches[1]);
    if (!$sessionUser) return new Reply([
      'message' => 'Session not found.',
      'actionId' => ReplyInterface::ACTION_SESSION_NOT_FOUND,
    ], 404);

    if (!$sessionUser->isActive()) {
      Session::delete($matches[1]);
      return new Reply([
        'message' => 'User is blocked.',
        'actionId' => ReplyInterface::ACTION_USER_BLOCKED,
      ], 403);
    }

    $context = $this->request->attributes->get('context', []);
    self::setRequestAttributes($this->request, 'context', [
      ...$context,
      'accessToken' => $matches[1],
      'user' => [
        'id' => $sessionUser->getEntityId(),
        'roles' => $sessionUser->getRoles(),
        'permissions' => $sessionUser->getPermissions(),
        'isActive' => true,
        'isAuthenticated' => true,
      ],
    ]);

    return null;
  }
}
