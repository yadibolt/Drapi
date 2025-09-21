<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\drift_eleven\Core\Cache\Cache;
use Drupal\drift_eleven\Core\Logger\Logger;
use Drupal\drift_eleven\Core\Logger\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigCacheControlSubscriber implements EventSubscriberInterface {
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'onConfigChange',
      ConfigEvents::DELETE => 'onConfigChange',
      ConfigEvents::IMPORT => 'onConfigChange',
      ConfigEvents::IMPORT_MISSING_CONTENT => 'onConfigChange',
      ConfigEvents::IMPORT_VALIDATE => 'onConfigChange',
      ConfigEvents::RENAME => 'onConfigChange',
      ConfigEvents::STORAGE_TRANSFORM_EXPORT => 'onConfigChange',
    ];
  }

  public function onConfigChange(ConfigCrudEvent $event): void {
    /*$entity = $event->getConfig()->getName();
    Logger::l('Config changed: @entityType', [ // TODO: REMOVE
      '@entityType' => $entity
    ], LoggerInterface::LEVEL_INFO);

    Cache::invalidate($entity);*/
  }
}
