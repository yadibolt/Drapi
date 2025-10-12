<?php

namespace Drupal\drift_eleven\Core\Http\Trait;

use Drupal;
use Symfony\Component\HttpFoundation\Request;

trait RequestTrait {
  protected function addAttributes(Request $request, string $key, array $values): void {
   $request->attributes->set($key, $values);
  }
  protected function getCurrentRequest(): Request {
    return Drupal::service('request_stack')->getCurrentRequest();
  }
  protected function getCurrentRoute(): array {
    $routeId = $this->getCurrentRequest()->attributes->get('_route');

    if ($routeId) {
      $configuration = Drupal::configFactory()->get(ROUTE_CONFIG_NAME_DEFAULT);
      $routeRegistry = $configuration->get('route_registry');

      if (isset($routeRegistry[$routeId])) {
        return $routeRegistry[$routeId];
      }
    }

    return [];
  }
}
