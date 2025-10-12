<?php

namespace Drupal\drift_eleven\Core\Http\Route;

use Drupal\drift_eleven\Core\Http\Route\Asserters\RouteDocCommentAsserter;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteBase;
use Drupal\drift_eleven\Core\Utility\Enum\LoggerIntent;
use Drupal\drift_eleven\Core\Utility\Logger;
use Exception;

class Route extends RouteBase {
  /**
   * @throws Exception
   */
  public static function make(string $id, string $name, string $method, string $description, string $path, array $permissions, array $roles, array $useMiddleware, bool $useCache, string $filePath = ''): self {
    return new self($id, $name, $method, $description, $path, $permissions, $roles, $useMiddleware, $useCache, $filePath);
  }
  public static function fromDocComment(string $filePath): ?self {
    $classDocComment = self::getFileDocComment($filePath);
    if (empty($classDocComment)) {
      Logger::l(
        level: LoggerIntent::ERROR, message: 'Route base doc comment is empty.'
      );
    }

    $values = RouteDocCommentAsserter::parseDocComment($classDocComment);
    try {
      return self::make(
        id: $values['id'],
        name: $values['name'],
        method: $values['method'],
        description: $values['description'] ?? '',
        path: $values['path'],
        permissions: $values['permissions'] ?? [],
        roles: $values['roles'] ?? [],
        useMiddleware: $values['useMiddleware'] ?? [],
        useCache: $values['useCache'] ?? false,
        filePath: $filePath
      );
    } catch (Exception $e) {
      Logger::l(
        level: LoggerIntent::ERROR,
        message: 'Error creating Route from doc comment: @error',
        context: ['@error' => $e->getMessage()]
      );
    }

    return null;
  }
}
