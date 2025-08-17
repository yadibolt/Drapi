<?php

namespace Drupal\pingvin\Route;

use Drupal;
use Drupal\pingvin\File\Retriever;
use Drupal\pingvin\Parser\RouteDocCommentParser;
use Exception;

class RouteBuilder {
  protected string $routeFilePath;

  /**
   * Constructs a new RouteBuilder instance.
   *
   * @param string $routeFilePath
   *    The path to the route file.
   *
   * @throws Exception
   *    If the route file does not exist, is not readable, or is not a valid PHP file.
   */
  public function __construct(string $routeFilePath) {
    if (!file_exists($routeFilePath)) {
      throw new Exception("The route file at {$routeFilePath} does not exist.");
    }

    if (!is_readable($routeFilePath)) {
      throw new Exception("The route file at {$routeFilePath} is not readable.");
    }

    if (!is_file($routeFilePath)) {
      throw new Exception("The path {$routeFilePath} is not a valid file.");
    }

    if (pathinfo($routeFilePath, PATHINFO_EXTENSION) !== 'php') {
      throw new Exception("The file at {$routeFilePath} is not a PHP file.");
    }

    $this->routeFilePath = $routeFilePath;
  }

  /**
   * Builds a Route instance from the route file path.
   *
   * @return Route
   *    Returns a Route instance built from the doc comment in the route file.
   *
   * @throws Exception
   *    If the doc comment is not parsable or contains errors.
   */
  public function buildFromPath(): Route {
    $config = Drupal::configFactory()->getEditable(pw8dr1_PROJECT_ID);
    if (!$config->get('route_registry')) {
      $config->set('route_registry', []);
    }

    $configRouteRegistry = $config->get('route_registry');

    // create new instance of a Route
    $content = new Retriever($this->routeFilePath)->retrieve('docComment');
    $routeContent = new RouteDocCommentParser($content)->parse(true);
    $route = new Route($this->routeFilePath, $routeContent);

    if (isset($configRouteRegistry[$route->getId()])) {
      // if the route already exists, we override it
      // except for the 'enabled' prop. We want to keep that
      // because of the config that user is able to set
      $route->setEnabled($configRouteRegistry[$route->getId()]['enabled']);
    }

    return $route;
  }
}
