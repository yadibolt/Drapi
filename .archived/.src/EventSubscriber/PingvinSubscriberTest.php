<?php

namespace Drupal\pingvin\EventSubscriber;

use Drupal\pingvin\Http\PingvinResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

class PingvinSubscriberTest implements EventSubscriberInterface {
  public static function getSubscribedEvents(): array
  {
    // Listen early so we can override the response
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 100],
    ];
  }

  public function onKernelRequest(RequestEvent $event): void
  {
    $e = \Drupal::cache('pingvin')->get('pingvin_url');

    \Drupal::logger('pingvin')->info('Pingvin Subscriber Test @d', ['@d' => print_r($e, true)]);

    if ($e) {
      $d = json_decode($e->data, true);
      $d['p'] = 'p';

      $event->setResponse(new PingvinResponse($d, 200));
    }
  }
}
