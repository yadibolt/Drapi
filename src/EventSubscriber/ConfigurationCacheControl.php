<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\drift_eleven\Core\Cache\Cache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigurationCacheControl implements EventSubscriberInterface{
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'onConfigChange',
      ConfigEvents::DELETE => 'onConfigChange',
      ConfigEvents::RENAME => 'onConfigChange',
    ];
  }
  public function onConfigChange(ConfigCrudEvent $event): void {
    Cache::make()->invalidateEntityTags($event->getConfig()->getName());
  }
}
