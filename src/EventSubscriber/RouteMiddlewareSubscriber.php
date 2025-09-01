<?php

namespace Drupal\pingvin\EventSubscriber;

use Drupal\pingvin\Cache\PingvinCache;
use Drupal\pingvin\Http\PingvinResponse;
use Drupal\pingvin\Route\Cache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;

class RouteMiddlewareSubscriber implements EventSubscriberInterface {
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::CONTROLLER => 'onKernelController',
    ];
  }

  public function onKernelController(ControllerEvent $event): ?PingvinResponse
  {
    $request = $event->getRequest();

    /** @var Route $route */
    $route = $request->attributes->get('_route_object');

    if ($route instanceof Route) {
      $options = $route->getOptions();

      if (isset($options[pw8dr1_PROJECT_ID . ':routeId'])) {
        $url = str_replace($request->getBaseUrl(), '', $request->getRequestUri());


        // premature return
        /*$event->setController(function() {
          return new PingvinResponse([
            'title' => "Hello World!",
          ], 200);
        });*/

        // todo: after all middleware bs

        // todo: implement APCU cache

        $start = microtime(true);
        $hit = PingvinCache::use($url);
        $end = microtime(true);
        \Drupal::logger('t')->info('@t', ['@t' => ($end-$start)*1000]);
        if ($hit) {
          $event->setController(function() use ($hit) {
            \Drupal::logger('pingvin')->info('Cache hit: returning');
            return new PingvinResponse($hit['content'], $hit['status'], $hit['headers'], true);
          });
        }
      }
    }

    return null;
  }

}
