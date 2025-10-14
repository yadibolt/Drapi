<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Drupal\drift_eleven\Core\Http\Middleware\Middleware;
use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\EventSubscriber\Trait\RouteTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MiddlewareControl implements EventSubscriberInterface {
  use RouteTrait;

  protected const int PRIORITY = 998;

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', self::PRIORITY],
    ];
  }

  public function onKernelRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) return;

    $request = $event->getRequest();

    [$route, ,] = $this->getCurrentRoute($request, false);

    if (!$route) return;

    $middlewareResponse = new Middleware()->apply($route);
    if ($middlewareResponse instanceof Reply) {
      $event->setResponse($middlewareResponse);
      $event->stopPropagation();
    }
  }
}
