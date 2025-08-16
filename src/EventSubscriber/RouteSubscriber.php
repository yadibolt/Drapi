<?php

namespace Drupal\pingvin\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {
  protected function alterRoutes(RouteCollection $collection): void {}
}

