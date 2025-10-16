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

    $routeReg = RouteRegistry::make(ROUTE_CONFIG_CORE_ROUTES_PATH_DEFAULT);
    $routeReg->scanDirectory(); // register core routes
    $routeReg->setDirectoryPath(ROUTE_CONFIG_CUSTOM_ROUTES_PATH_DEFAULT);
    $routeReg->scanDirectories('Ext'); // register custom routes

    foreach ($routeReg->getRegistry() as $route) {
      $name = ROUTE_NAME_PREFIX_DEFAULT . ':' . $route->getId();
      $collection->add($name, $route->toSymfonyRoute());
      $routeRegistry[$name] = $route->toArray();
    }

    $configuration->set('route_registry', $routeRegistry);
    $configuration->save();
  }
}
