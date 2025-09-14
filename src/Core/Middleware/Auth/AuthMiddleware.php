<?php

namespace Drupal\drift_eleven\Core\Middleware\Auth;

use Drupal\drift_eleven\Core\HTTP\Reply;
use Drupal\drift_eleven\Core\Middleware\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthMiddleware implements MiddlewareInterface {
  protected Request $request;
  protected array $route;

  public function __construct(Request $request, array $route = []) {
    $this->request = $request;
    $this->route = $route;
  }

  public function run(): ?Reply {
    return new Reply([
      'message' => 'Auth processed successfully.'
    ], 200);
    return null;
  }

  protected function extendRequestAttributes(array $attrs): void {
    foreach ($attrs as $key => $value) {
      $this->request->attributes->set($key, $value);
    }
  }
}
