<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Drupal;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\drift_eleven\Core\Route\Route;
use Drupal\drift_eleven\Core\Route\RouteRegistry;
use InvalidArgumentException;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
      $config = Drupal::configFactory()->getEditable(D9M7_CONFIG_KEY);
      $routeRegistry = $config->get('routeRegistry') ?: [];

      $coreRoutes = RouteRegistry::scanDir(D9M7_CORE_ROUTES_DIR);
      // todo: add user routes dir scan

      foreach ([$coreRoutes] as $routeCollection) {
          if (!is_array($routeCollection)) throw new InvalidArgumentException('RouteCollection must be an array.');

          /** @var Route $route */
          foreach ($routeCollection as $route) {
              $collection->add("drift_eleven:route:{$route->getId()}", $route->toSymfony());
              $routeRegistry["drift_eleven:route:{$route->getId()}"] = $route->toArray();
          }

          // we wipe the config for routeRegistry as we hydrated it
          // this prevents the issues with registering routes, duplicates and/or cache issues
          // within the config
          $config->set('routeRegistry', $routeRegistry);
          $config->save();
      }
  }
}
