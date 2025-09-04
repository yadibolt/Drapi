<?php

namespace Drupal\pingvin\EventSubscriber;

use Drupal\config_collection_install_test\EventSubscriber;
use Drupal\pingvin\Cache\PingvinCache;
use Drupal\pingvin\Http\PingvinResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;

class TestEventSubscriber implements EventSubscriberInterface {
  public function onKernelRequest(RequestEvent $event): void
  {
    $request = $event->getRequest();

    $url = str_replace($request->getBaseUrl(), '', $request->getRequestUri());

    $hit = PingvinCache::use($url);

    if ($hit) {
      $event->setResponse(new Response(':)'));
    }
  }

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 1],
    ];
  }
}
