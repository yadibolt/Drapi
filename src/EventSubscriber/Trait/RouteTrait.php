<?php

namespace Drupal\drift_eleven\EventSubscriber\Trait;

use Drupal;
use Symfony\Component\HttpFoundation\Request;

trait RouteTrait {
  public function getCurrentRoute(Request $request, bool $withQueryParams = true): array {
    $configuration = Drupal::configFactory()->get(ROUTE_CONFIG_NAME_DEFAULT);
    $routeRegistry = $configuration->get('route_registry') ?? [];

    $uri = ltrim($request->getRequestUri(), '/');
    if (!$withQueryParams) {
      $uri = explode('?', $uri)[0];
    }

    $uriParts = mb_split('/', $uri);

    foreach ($routeRegistry as $route) {
      if (!isset($route['path'])) continue;

      $parts = mb_split('/', $route['path']);
      if (count($parts) !== count($uriParts)) continue;

      for ($i = 0; $i < count($parts); $i++) {
        if (str_starts_with($parts[$i], '{') && str_ends_with($parts[$i], '}')) continue;
        if ($parts[$i] !== $uriParts[$i]) continue 2;
      }

      return [$route, $configuration];
    }

    return [null, $configuration];
  }
}
