<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Drupal;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\drift_eleven\Core\Http\Route\RouteRegistry;
use Symfony\Component\Routing\RouteCollection;

class Routes extends RouteSubscriberBase {
  protected function alterRoutes(RouteCollection $collection): void {
    $configuration = Drupal::configFactory()->getEditable(ROUTE_CONFIG_NAME_DEFAULT);
    $routeRegistry = $configuration->get('route_registry') ?: [];

    $coreRoutes = RouteRegistry::make(ROUTE_CONFIG_CORE_ROUTES_PATH_DEFAULT)->scanDirectory();
    $otherRoutes = []; // TODO implement scanning other module dirs for routes

    foreach ([$coreRoutes, $otherRoutes] as $routeCollection) {
      if (!is_array($routeCollection) || empty($routeCollection)) continue;

      foreach ($routeCollection as $route) {
        $name = ROUTE_NAME_PREFIX_DEFAULT . ':' . $route->getId();
        $collection->add($name, $route->toSymfonyRoute());
        $routeRegistry[$name] = $route->toArray();
      }
    }

    $configuration->set('route_registry', $routeRegistry);
    $configuration->save();
  }
}
