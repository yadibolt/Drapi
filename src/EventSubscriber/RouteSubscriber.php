<?php

namespace Drupal\pingvin\EventSubscriber;

use Drupal;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\pingvin\Registry\RouteRegistry;
use Drupal\pingvin\Route\RouteBuilder;
use Exception;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   *
   * @param RouteCollection $collection
   *    Parameter containing the collection of routes.
   * @return void
   * @throws Exception
   *    If the provided directory does not exist or if a route file is invalid.
   */
  protected function alterRoutes(RouteCollection $collection): void {
    $configCtx = pw8dr1_PROJECT_ID . '.settings';
    $config = Drupal::configFactory()->getEditable($configCtx);
    $configRoutes = [];

    $userRouteRegistry = new RouteRegistry(pw8dr1_USER_ROUTES_DIR)->registerRoutes();
    // $coreRouteRegistry = new RouteRegistry(CORE_ROUTES_DIR)->registerRoutes();
    $coreRouteRegistry = [];

    foreach ([...$userRouteRegistry, ...$coreRouteRegistry] as $registryPath) {
      $route = new RouteBuilder($registryPath)->buildFromPath();

      // add routes to the collection
      $collection->add(pw8dr1_PROJECT_ID . ':' . $route->getId(), $route->getSymfonyRoute());
      $configRoutes[$route->getId()] = $route->getAssociativeRoute();
    }

    // we wipe the config for route_registry as we hydrated it
    // this prevents the issues with registering routes, duplicates and/or cache issues
    // within the config
    $config->set('route_registry', $configRoutes);
    $config->save();
  }
}

