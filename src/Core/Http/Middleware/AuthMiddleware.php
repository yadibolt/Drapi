<?php

namespace Drupal\drapi\Core\Http\Middleware;

use Drupal\drapi\Core\Auth\JWT;
use Drupal\drapi\Core\Http\Enum\ReplyIntent;
use Drupal\drapi\Core\Http\Middleware\Base\MiddlewareBase;
use Drupal\drapi\Core\Http\Middleware\Interface\MiddlewareInterface;
use Drupal\drapi\Core\Http\Reply;
use Drupal\drapi\Core\Http\Route\Route;use Drupal\drapi\Core\Session\Enum\SubjectIntent;
use Drupal\drapi\Core\Session\Session;
use Drupal\drapi\Core\Session\Subject;

class AuthMiddleware extends MiddlewareBase implements MiddlewareInterface {
  public static function make(Route $route): self {
    return new self($route);
  }
  public static function getId(): string {
    return 'auth';
  }
  public function process(): ?Reply {
    $authorizationHeader = $this->currentRequest->headers->get('authorization');
    if (empty($authorizationHeader) || !preg_match('/^Bearer\s+(\S+)$/', $authorizationHeader, $matches)) {
      // we automatically assume this is anonymous call
      $subject = Subject::makeAnonymous();
      if (!$this->checkRequirements($subject)) return Reply::make([
        'action_id' => ReplyIntent::REQUIREMENTS_NOT_MET,
        'message' => 'Requirements not met.',
      ], status: 403);

      $requestContext = $this->currentRequest->attributes->get('context', []);
      $this->addAttributes($this->currentRequest, 'context', [
        ...$requestContext,
        'token' => null,
        'user' => $subject,
      ]);

      return null;
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

    $routePermissions = $route->getPermissions() ?? [];
    $routeRoles = $route->getRoles() ?? [];

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
    if (!isset($payload['data'])) return false;

    if (!isset($payload['data']['user_id'])) return false;
    if (!is_numeric($payload['data']['user_id'])) return false;

    if (!isset($payload['data']['type'])) return false;
    if (!is_string($payload['data']['type'])) return false;

    if (!in_array($payload['data']['type'], [SubjectIntent::AUTHENTICATED->value, SubjectIntent::ANONYMOUS->value])) return false;
    if ((int)$payload['data']['user_id'] <= 0 && $payload['data']['type'] === SubjectIntent::AUTHENTICATED->value) return false;

    return true;
  }
}
