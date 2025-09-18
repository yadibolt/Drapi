<?php

namespace Drupal\drift_eleven\Core\Middleware\Request;

use Drupal\drift_eleven\Core\HTTP\Request\RequestAttributesTrait;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Middleware\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestMiddleware implements MiddlewareInterface {
  use RequestAttributesTrait;

  protected Request $request;
  protected array $route;

  public function __construct(Request $request, array $route = []) {
    $this->request = $request;
    $this->route = $route;
  }

  public function run(): ?Reply {
    $headerLanguage = $this->request->headers->get('Accept-Language', 'en');

    $context = $this->request->attributes->get('context', []);
    self::setRequestAttributes($this->request, 'context', [
      ...$context,
      'request' => [
        'langcode' => $headerLanguage,
      ],
    ]);

    return null;
  }
}
