<?php

namespace Drupal\drift_eleven\EventSubscriber2;

use Drupal;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\drift_eleven\Core2\Utility\Enum\LoggerIntent;
use Drupal\drift_eleven\Core2\Utility\Logger;
use Exception;
use Symfony\Component\Routing\RouteCollection;

class Routes extends RouteSubscriberBase {
  protected function alterRoutes(RouteCollection $collection): void {
    $configuration = Drupal::configFactory()->getEditable(ROUTE_CONFIG_NAME_DEFAULT);
    $routeRegistry = $configuration->get('route_registry') ?: [];

    $coreRoutes = RouteRegistry::make()->scanDirectory(ROUTE_CONFIG_CORE_ROUTES_PATH_DEFAULT);
    $otherRoutes = []; // TODO implement scanning other module dirs for routes

    foreach ([$coreRoutes, $otherRoutes] as $routeCollection) {
      if (!is_array($routeCollection) || empty($routeCollection)) continue;

      foreach ($routeCollection as $route) {
        try {
          if (!RouteRegistry::validate($route)) continue;
        } catch (Exception $e) {
          Logger::l(
            level: LoggerIntent::ERROR,
            message: 'Route validation failed. Please check the route definition and try again. @error',
            context: ['@error' => $e->getMessage()]
          );
        }

        $name = ROUTE_NAME_PREFIX_DEFAULT . ':' . $route->getId();
        $collection->add($name, $route->toSymfonyRoute());
        $routeRegistry[$name] = $route->toArray();
      }
    }

    $configuration->set('route_registry', $routeRegistry);
    $configuration->save();
  }
}
