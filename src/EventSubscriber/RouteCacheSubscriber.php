<?php

namespace Drupal\pingvin\EventSubscriber;

use Drupal\pingvin\Http\PingvinResponse;
use Drupal\pingvin\Route\Cache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;

class RouteCacheSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => 'onKernelResponse',
    ];
  }

  public function onKernelResponse(ResponseEvent $event): void {
    $response = $event->getResponse();
    $request = $event->getRequest();

    /** @var Route $route */
    $route = $request->attributes->get('_route_object');

    $url = null;

    if ($route instanceof Route) {
      $options = $route->getOptions();

      if (isset($options[pw8dr1_PROJECT_ID . ':routeId'])) {
        $url = str_replace($request->getBaseUrl(), '', $request->getRequestUri());
        \Drupal::logger('pingvin')->info('@d', ['@d' => print_r($url, true)]);

        // premature return
        /*$event->setController(function() {
          return new PingvinResponse([
            'title' => "Hello World!",
          ], 200);
        });*/
      }
    }

    if ($response instanceof PingvinResponse) {
      // todo: replace Drupal cache stuff
      // \Drupal::logger('pingvin')->info('Caching this shit: @d', ['@d' => print_r($response->headers->all(), true)]);

      if ($response->headers->get('x-pingvin-cache') == 'CACHEABLE') {
        \Drupal::logger('pingvin')->info('This BS:@d', ['@d' => print_r($url, true)]);
        $this->cacheResponse($response, $url);
      }
    }
  }

  private function cacheResponse(PingvinResponse $response, string $url): void {
    $data = [
      'content' => $response->getContent(),
      'status' => $response->getStatusCode(),
      'headers' => $response->headers->all(),
      // 'body' => ???
    ];

    Cache::create($url, $data, Cache::DURATION_DEFAULT);

    \Drupal::logger('pingvin')->info('Caching this shit: @d', ['@d' => print_r($data, true)]);
  }
}
