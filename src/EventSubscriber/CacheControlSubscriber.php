<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CacheControlSubscriber implements EventSubscriberInterface {
  protected const int PRIORITY   = 100;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', self::PRIORITY],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function onKernelRequest(RequestEvent $event): void {
    // todo: handle cache + middleware if applicable
  }
}
