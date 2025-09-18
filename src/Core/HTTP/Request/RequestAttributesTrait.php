<?php

namespace Drupal\drift_eleven\Core\HTTP\Request;

use Symfony\Component\HttpFoundation\Request;

trait RequestAttributesTrait {
  public static function setRequestAttributes(Request $request, string $key, array $values): void {
    $request->attributes->set($key, $values);
  }
}
