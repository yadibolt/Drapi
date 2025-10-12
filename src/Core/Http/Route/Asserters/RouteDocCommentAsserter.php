<?php

namespace Drupal\drift_eleven\Core\Http\Route\Asserters;

use Drupal\drift_eleven\Core\Http\Route\Asserters\Interface\RouteAsserterInterface;
use Drupal\drift_eleven\Core\Utility\Enum\LoggerIntent;
use Drupal\drift_eleven\Core\Utility\Logger;
use Drupal\user\Entity\Role;

class RouteDocCommentAsserter implements RouteAsserterInterface {
  protected const array ROUTE_TAGS = [
    'id',
    'name',
    'method',
    'description',
    'path',
    'permissions',
    'roles',
    'useMiddleware',
    'useCache',
    'enabled'
  ];

  public static function assert($route): bool {
    $classDocComment = $route->getClassDocComment();

    // 1. Class must have a doc comment
    if (empty($classDocComment)) return false;

    $classDocComment = self::parseDocComment($classDocComment);
    if (empty($classDocComment)) return false;

    // 2. Doc comment must have required attributes (attributes with "*" are required)

    // *@id
    if (!isset($classDocComment['id']) || !is_string($classDocComment['id'])) return false;
    // *@name
    if (!isset($classDocComment['name']) || !is_string($classDocComment['name'])) return false;
    // *@method
    if (!isset($classDocComment['method']) || !is_string($classDocComment['method'])) return false;
    if (!in_array(strtoupper($classDocComment['method']), $route::ALLOWED_HTTP_METHODS)) return false;
    // *@path
    if (!isset($classDocComment['path']) || !is_string($classDocComment['path'])) return false;
    if (str_starts_with($classDocComment['path'], '/') || str_ends_with($classDocComment['path'], '/')) return false;

    // optional
    // @description
    if (isset($classDocComment['description']) && !is_string($classDocComment['description'])) return false;
    // @permissions
    if (isset($classDocComment['permissions']) && !is_array($classDocComment['permissions'])) return false;
    // @roles
    if (isset($classDocComment['roles']) && !is_array($classDocComment['roles'])) return false;
    // check if role exists here
    if (isset($classDocComment['roles'])) {
      if (array_any($classDocComment['roles'], fn($role) => !Role::load($role))) {
        return false;
      }
    }
    // @useMiddleware
    if (isset($classDocComment['useMiddleware']) && !is_array($classDocComment['useMiddleware'])) return false;
    if (isset($classDocComment['useMiddleware'])) {
      if (array_any($classDocComment['useMiddleware'], fn($middleware) => !in_array($middleware, $route::ALLOWED_MIDDLEWARES))) {
        return false;
      }
    }
    // @useCache
    if (isset($classDocComment['useCache']) && !is_bool($classDocComment['useCache'])) return false;

    return true;
  }
  public static function parseDocComment(string $content, bool $fillMissingKeys = false): ?array {
    if (!str_starts_with($content, '/**') || !str_ends_with($content, '*/')) {
      Logger::l(
        level: LoggerIntent::ERROR, message: 'Doc comment must start with /** and end with */'
      ); return null;
    }

    if (!preg_match('/@route\s+/', $content)) {
      Logger::l(
        level: LoggerIntent::ERROR, message: 'Doc comment must contain a @route tag.'
      ); return null;
    }

    if (!preg_match('/@route-end\s+/', $content)) {
      Logger::l(
        level: LoggerIntent::ERROR, message: 'Doc comment must contain a @route-end tag.'
      ); return null;
    }

    $start = strpos($content, '@route');
    $end = strpos($content, '@route-end', $start);

    if ($start > $end) {
      Logger::l(
        level: LoggerIntent::ERROR, message: 'The @route-end tag must come after the @route tag.'
      ); return null;
    }

    $values = substr($content, $start, $end - $start);
    $values = str_replace('=', ':', $values);
    $values = str_replace(['@route', '@route-end', '*'], '', $values);
    $values = str_replace("'", '"', $values);
    $values = trim($values);

    $variablesCount = 0;
    foreach (self::ROUTE_TAGS as $tag) {
      $pattern = '/\b' . preg_quote($tag, '/') . '\b/';
      if (!preg_match($pattern, $values)) continue;

      if ($variablesCount === 0) {
        $values = preg_replace($pattern, '"' . $tag . '"', $values, 1);
      } else {
        $values = preg_replace($pattern, ',"' . $tag . '"', $values, 1);
      }

      $variablesCount++;
    }

    $valuesParsed = json_decode('{' . $values . '}', true);
    if (empty($valuesParsed)) {
      Logger::l(
        level: LoggerIntent::ERROR, message: 'Failed to parse route values from doc comment.'
      ); return null;
    }

    if ($fillMissingKeys) {
      foreach (self::ROUTE_TAGS as $tag) {
        if (!array_key_exists($tag, $valuesParsed)) {
          if ($tag === 'enabled') {
            $valuesParsed[$tag] = true;
          } else {
            $valuesParsed[$tag] = null;
          }
        }
      }
    }

    return $valuesParsed ?? null;
  }
}
