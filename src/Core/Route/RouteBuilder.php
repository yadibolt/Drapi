<?php

namespace Drupal\drift_eleven\Core\Route;

use Drupal;
use Drupal\drift_eleven\Core\File\FileAttributeRetriever;
use Drupal\drift_eleven\Core\File\FileTrait;
use Drupal\drift_eleven\Core\Parser\RouteDocCommentParser;
use Exception;
use InvalidArgumentException;

class RouteBuilder implements RouteBuilderInterface {
  use FileTrait;
force-commit
  /**
   * @throws Exception
   */
  public static function build(string $filePath): Route {
    if (!self::fileExists($filePath) || !self::isReadable($filePath) || !self::isValidPHPFile($filePath)) {
      throw new InvalidArgumentException("File $filePath does not exist or is not readable or is not a valid PHP file.");
    }

    $config = Drupal::configFactory()->get(D9M7_CONFIG_KEY);
    $routeRegistry = $config->get('routeRegistry') ?: [];

    $docComment = FileAttributeRetriever::retrieve($filePath, 'docComment');
    $routeDef = RouteDocCommentParser::parse($docComment, true);
    $route = new Route(
      id: $routeDef['id'],
      name: $routeDef['name'],
      method: $routeDef['method'],
      description: $routeDef['description'] ?: '',
      path: $routeDef['path'],
      permissions: $routeDef['permissions'] ?: [],
      roles: $routeDef['roles'] ?: [],
      useMiddleware: $routeDef['useMiddleware'] ?: [],
      useCache: $routeDef['useCache'] ?: false
    );

    // additionally, we set the file path so we can use FileAttributeRetriever
    // to retrieve other attributes if needed
    $route->setFilePath($filePath);

    if (isset($routeRegistry[$route->getId()])) {
      // we later override all properties except enabled
      // because that prop is managed by the user preference
      $enabled = $routeRegistry[$route->getId()]['enabled'] ?: true;
      $route->setEnabled($enabled);
    }

    return $route;
  }
}
