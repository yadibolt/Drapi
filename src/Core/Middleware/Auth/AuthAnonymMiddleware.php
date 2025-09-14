<?php

namespace Drupal\drift_eleven\Core\Middleware\Auth;

use Drupal\drift_eleven\Core\Auth\JsonWebToken;
use Drupal\drift_eleven\Core\HTTP\Request\RequestAttributesTrait;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\HTTP\Response\ReplyInterface;
use Drupal\drift_eleven\Core\Middleware\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthAnonymMiddleware implements MiddlewareInterface {
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
    if (!$isOk->valid || $isOk->error) {
      return new Reply([
        'message' => 'Invalid token.',
        'actionId' => ReplyInterface::ACTION_INVALID_TOKEN,
      ], 401);
    }

    $payload = $jsonWebToken->payloadFrom($matches[1]);
    if (empty($payload) || !isset($payload['userId']) || !is_numeric($payload['userId'])) {
      return new Reply([
        'message' => 'Invalid token payload.',
        'actionId' => ReplyInterface::ACTION_INVALID_PAYLOAD,
      ], 401);
    }

    if ($payload['userId'] > 0) {
      return new Reply([
        'message' => 'Already logged in.',
        'actionId' => ReplyInterface::ACTION_ALREADY_LOGGED_IN,
      ], 403);
    }
    // todo: add context

    return null;
  }
}
