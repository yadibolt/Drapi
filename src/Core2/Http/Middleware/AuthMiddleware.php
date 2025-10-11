<?php

namespace Drupal\drift_eleven\Core2\Http\Middleware;

use Drupal\drift_eleven\Core2\Auth\JWT;
use Drupal\drift_eleven\Core2\Http\Enum\ReplyIntent;
use Drupal\drift_eleven\Core2\Http\Middleware\Base\MiddlewareBase;
use Drupal\drift_eleven\Core2\Http\Middleware\Interface\MiddlewareInterface;
use Drupal\drift_eleven\Core2\Http\Reply;

class AuthMiddleware extends MiddlewareBase implements MiddlewareInterface {
  public static function make(): self {
    return new self();
  }
  public function getId(): string {
    return 'auth';
  }
  public function process(): ?Reply {
    $authorizationHeader = $this->currentRequest->headers->get('authorization');
    if (empty($authorizationHeader) || !preg_match('/^Bearer\s+(\S+)$/', $authorizationHeader, $matches)) {
      return Reply::make(
        data: [
          'action_id' => ReplyIntent::INVALID_HEADER,
          'message' => 'Authorization header is missing.',
        ], status: 401
      );
    }

    $checked = JWT::check($matches[1]);
    if (!$checked->isValid() || $checked->isExpired() || $checked->hasError()) {
      return Reply::make(
        data: [
          'action_id' => ReplyIntent::INVALID_TOKEN,
          'message' => 'Token is not valid.',
        ], status: 403
      );
    }

    $payload = JWT::payloadFrom($matches[1]);
    if (!$this->checkPayload($payload)) {
      return Reply::make(
        data: [
          'action_id' => ReplyIntent::INVALID_PAYLOAD,
          'message' => 'Payload is not valid.',
        ], status: 400
      );
    }

    if ($payload['data']['type'] === SubjectIntent::ANONYMOUS) {
      $subject = Session::make($matches[1])->getSubject();

      if (!$this->checkRequirements($subject)) return Reply::make([
        'action_id' => ReplyIntent::REQUIREMENTS_NOT_MET,
        'message' => 'Requirements not met.',
      ], status: 403);

      $requestContext = $this->currentRequest->attributes->get('context', []);
      $this->addAttributes($this->currentRequest, 'context', [
        ...$requestContext,
        'token' => $matches[1],
        'user' => $subject,
      ]);

      return null;
    }

    $subject = Session::make($matches[1])->find()?->getSubject();
    if (!$subject) return Reply::make([
      'action_id' => ReplyIntent::SESSION_NOT_FOUND,
      'message' => 'Unauthorized access.',
    ], status: 404);

    if (!$subject->isActive()) return Reply::make([
      'action_id' => ReplyIntent::UNAUTHORIZED,
      'message' => 'Unauthorized access.',
    ], status: 403);

    if (!$this->checkRequirements($subject)) return Reply::make([
      'action_id' => ReplyIntent::UNAUTHORIZED,
      'message' => 'Requirements not met.',
    ], status: 403);

    $requestContext = $this->currentRequest->attributes->get('context', []);
    $this->addAttributes($this->currentRequest, 'context', [
      ...$requestContext,
      'token' => $matches[1],
      'user' => $subject,
    ]);

    return null;
  }

  protected function checkRequirements(Subject $subject): bool {
    $route = $this->currentRoute;

    $routePermissions = $route['permissions'] ?? [];
    $routeRoles = $route['roles'] ?? [];

    $permissions = $subject->getPermissions();
    if (array_any($routePermissions,fn($routePermission) => !in_array($routePermission, $permissions))) {
      return false;
    }

    $roles = $subject->getRoles();
    if (array_any($routeRoles, fn($routeRole) => !in_array($routeRole, $roles))) {
      return false;
    }

    return true;
  }
  protected function checkPayload(array $payload): bool {
    if (empty($payload)) return false;
    if (!isset($payload['data']))

    if (!isset($payload['data']['user_id'])) return false;
    if (!is_numeric($payload['data']['user_id'])) return false;
    if ((int)$payload['data']['user_id'] <= 0) return false;

    if (!isset($payload['data']['type'])) return false;
    if (!is_string($payload['data']['type'])) return false;
    if (!in_array($payload['data']['type'], [SubjectIntent::AUTHENTICATED, SubjectIntent::ANONYMOUS])) return false;

    return true;
  }
}
