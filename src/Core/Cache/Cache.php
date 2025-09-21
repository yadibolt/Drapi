<?php

namespace Drupal\drift_eleven\Core\Cache;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\drift_eleven\Core\Logger\Logger;
use stdClass;

class Cache implements CacheInterface {
  public static function find(string $key): ?array {
    $record = Drupal::cache(self::CACHE_BIN_KEY)->get($key);
    return !empty($record) ? self::format($record) : null;
  }

  public static function make(string $key, mixed $data, int $cacheDuration = self::DURATION_DEFAULT, array $cacheTags = []): void {
    /*
     * Examples of cache tags (https://www.drupal.org/docs/drupal-apis/cache-api/cache-tags)
     *
     * By convention, identifiers follow the format thing:identifier.
     * When there is no concept of multiple instances of a thing, the format is simply thing.
     * The only rule is that identifiers cannot contain spaces.
     *
     * node:5 — cache tag for Node entity 5 (invalidated whenever it changes)
     * user:3 — cache tag for User entity 3 (invalidated whenever it changes)
     * node_list — list cache tag for Node entities (invalidated whenever any Node entity is updated, deleted or created, i.e., when a listing of nodes may need to change). Applicable to any entity type in following format: {entity_type}_list.
     * node_list:article — list cache tag for the article bundle (content type). Applicable to any entity + bundle type in following format: {entity_type}_list:{bundle}.
     * config:node_type_list — list cache tag for Node type entities (invalidated whenever any content types are updated, deleted or created). Applicable to any entity type in the following format: config:{entity_bundle_type}_list.
     * config:system.performance — cache tag for the system.performance configuration
     * library_info — cache tag for asset libraries
     */

    if (self::find($key)) return;
    Drupal::cache(self::CACHE_BIN_KEY)->set($key, $data, time() + $cacheDuration, $cacheTags);
  }

  public static function invalidate(string $key): void {
    if (self::find($key)) {
      Drupal::cache(self::CACHE_BIN_KEY)->invalidate($key);
    }
  }

  public static function invalidateEntity(EntityInterface $entity): void {
    if ($entity->getEntityTypeId() === 'menu_link_content') Cache::invalidate('menu_link_content');
    foreach ($entity->getCacheTags() as $cacheTag) {
      Logger::l('Invalidated entity cachetag: @cachetag', ['@cachetag' => $cacheTag]);
      Cache::invalidate($cacheTag);
    }
  }

  public static function flush(): void {
    Drupal::cache(self::CACHE_BIN_KEY)->deleteAll();
  }

  public static function format(stdClass $cacheRecord): ?array {
    return !empty($cacheRecord->data) ? $cacheRecord->data : null;
  }
}
