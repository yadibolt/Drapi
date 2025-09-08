<?php

namespace Drupal\drift_eleven\Core\Asserters;

use Drupal\Core\Session\PermissionChecker;
use Drupal\drift_eleven\Core\Middleware\MiddlewareInterface;
use Drupal\drift_eleven\Core\Parser\RouteDocCommentParser;
use Drupal\drift_eleven\Core\Route\Route;
use Drupal\drift_eleven\Core\Route\RouteInterface;
use Drupal\user\Entity\Role;

class RouteDocCommentAsserter implements RouteAsserterInterface {
  public static function assert(Route $route): bool {
    $attributes = $route->getFileAttributes();
    $docComment = $attributes['docComment'];

    // 1. Class must have a doc comment
    if (empty($docComment)) return false;

    // 2. Doc comment must have required attributes
    $docComment = RouteDocCommentParser::parse($docComment, true);

    // *@id
    if (!isset($docComment['id']) || !is_string($docComment['id'])) return false;
    // *@name
    if (!isset($docComment['name']) || !is_string($docComment['name'])) return false;
    // *@method
    if (!isset($docComment['method']) || !is_string($docComment['method'])) return false;
    if (!in_array(strtoupper($docComment['method']), RouteInterface::ALLOWED_HTTP_METHODS)) return false;
    // *@path
    if (!isset($docComment['path']) || !is_string($docComment['path'])) return false;
    if (str_starts_with($docComment['path'], '/') || str_ends_with($docComment['path'], '/')) return false;

    // optional
    // @description
    if (isset($docComment['description']) && !is_string($docComment['description'])) return false;
    // @permissions
    if (isset($docComment['permissions']) && !is_array($docComment['permissions'])) return false;
    // @roles
    if (isset($docComment['roles']) && !is_array($docComment['roles'])) return false;
    // check if role exists here
    if (isset($docComment['roles'])) {
      if (array_any($docComment['roles'], fn($role) => !Role::load($role))) {
        return false;
      }
    }
    // @useMiddleware
    if (isset($docComment['useMiddleware']) && !is_array($docComment['useMiddleware'])) return false;
    if (isset($docComment['useMiddleware'])) {
      if (array_any($docComment['useMiddleware'], fn($middleware) => !in_array($middleware, MiddlewareInterface::ALLOWED_MIDDLEWARES))) {
        return false;
      }
    }
    // @useCache
    if (isset($docComment['useCache']) && !is_bool($docComment['useCache'])) return false;

    return true;
  }
}
