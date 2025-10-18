<?php

namespace Drupal\drapi\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\drapi\Core\Cache\Cache;
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
    Cache::make(CACHE_BIN_KEY_DEFAULT)->invalidateEntityTags($event->getConfig()->getName());
  }
}
