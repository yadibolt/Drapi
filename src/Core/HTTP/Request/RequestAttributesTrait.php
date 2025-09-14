<?php

namespace Drupal\drift_eleven\Core\HTTP\Request;

use Symfony\Component\HttpFoundation\Request;

trait RequestAttributesTrait {
  public static function extendRequestAttributes(Request $request, array $attrs): void {
    foreach ($attrs as $key => $value) {
      $request->attributes->set($key, $value);
    }
  }
}
