<?php

namespace Drupal\drift_eleven\Core\Cache\Base;

use Drupal;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\drift_eleven\Core\Cache\Enum\CacheIntent;

class CacheBase {
  protected const string CACHE_BIN_KEY = CACHE_BIN_KEY_DEFAULT;
  protected const string CACHE_TAGS_BIN_KEY = CACHE_TAGS_BIN_KEY_DEFAULT;
  protected const int CACHE_DURATION = CACHE_DURATION_DEFAULT;

  protected string $binKey = self::CACHE_BIN_KEY;
  protected int $duration = self::CACHE_DURATION;

  public function __construct(string $binKey = self::CACHE_BIN_KEY, int $duration = self::CACHE_DURATION) {
    if (!empty($binKey)) {
      $this->binKey = $binKey;
    } else {
      $this->binKey = self::CACHE_BIN_KEY;
    }
    if ($duration > 0) {
      $this->duration = $duration;
    } else {
      $this->duration = self::CACHE_DURATION;
    }
  }

  public function get(string $key, CacheIntent $intent): mixed {
    $key = $this->makeKey($key, $intent);

    $record = Drupal::cache($this->binKey)->get($key);
    if (empty($record)) return null;

    return unserialize($record);
  }
  public function getCacheTags(): object|array {
    return Drupal::cache(self::CACHE_TAGS_BIN_KEY)->get('cache_tags') ?? [];
  }
  public function create(string $key, CacheIntent $intent, mixed $data, array $tags = []): bool {
    $key = $this->makeKey($key, $intent);

    if ($this->exists($key)) return false;
    $data = serialize($data);

    $tagsCacheBin = Drupal::cache(self::CACHE_TAGS_BIN_KEY);
    $cacheTags = $tagsCacheBin->get('cache_tags') ?? [];

    foreach ($tags as $tag) {
      if (!isset($cacheTags[$tag])) $cacheTags[$tag] = [];
      if (!isset($cacheTags[$tag][$key])) $cacheTags[$tag][$key] = 1;
    }

    Drupal::cache($this->binKey)->set($key, $data, $this->getCacheDurationTimestamp());
    $tagsCacheBin->set('cache_tags', $cacheTags, CACHE::PERMANENT);
    return true;
  }
  public function delete(string $key, CacheIntent $intent): void {
    $key = $this->makeKey($key, $intent);
    Drupal::cache($this->binKey)->delete($key);
  }
  public function flush(): void {
    Drupal::cache($this->binKey)->deleteAll();
    Drupal::cache(self::CACHE_TAGS_BIN_KEY)->deleteAll();
  }
  public function invalidateTags(array $tags): void {
    $cacheIdsToInvalidate = [];

    $tagsCacheBin = Drupal::cache(self::CACHE_TAGS_BIN_KEY);
    $cacheTags = $tagsCacheBin->get('cache_tags') ?? [];
    foreach ($tags as $tag) {
      if (isset($cacheTags[$tag]) && is_array($cacheTags[$tag])) {
        foreach ($cacheTags[$tag] as $cacheName => $_) {
          $cacheIdsToInvalidate[] = $cacheName;
        }
      }

      $cacheTags[$tag] = [];
    }

    Drupal::cache($this->binKey)->deleteMultiple($cacheIdsToInvalidate);
    $tagsCacheBin->set('cache_tags', $cacheTags, CACHE::PERMANENT);
  }
  public function invalidateEntityTags(EntityInterface|string $entity): void {
    $tagsCacheBin = Drupal::cache(self::CACHE_TAGS_BIN_KEY);
    $cacheTags = $tagsCacheBin->get('cache_tags') ?? [];

    if (is_string($entity)) {
      $cacheIdsToInvalidate = [];

      if (isset($cacheTags[$entity]) && is_array($cacheTags[$entity])) {
        foreach ($cacheTags[$entity] as $cacheName => $_) {
          $cacheIdsToInvalidate[] = $cacheName;
        }
        $cacheTags[$entity] = [];
      }

      Drupal::cache($this->binKey)->deleteMultiple($cacheIdsToInvalidate);
      $tagsCacheBin->set('cache_tags', $cacheTags, CACHE::PERMANENT);
      return;
    }

    if ($entity->getEntityTypeId() === 'menu_link_content') {
      $cacheIdsToInvalidate = [];

      if (isset($cacheTags['menu_link_content']) && is_array($cacheTags['menu_link_content'])) {
        foreach ($cacheTags['menu_link_content'] as $cacheName => $_) {
          $cacheIdsToInvalidate[] = $cacheName;
        }
        $cacheTags['menu_link_content'] = [];
      }

      Drupal::cache($this->binKey)->deleteMultiple($cacheIdsToInvalidate);
      $tagsCacheBin->set('cache_tags', $cacheTags, CACHE::PERMANENT);
      return;
    }

    $cacheIdsToInvalidate = [];

    foreach ($entity->getCacheTags() as $cacheTag) {
      if (isset($cacheTags[$cacheTag]) && is_array($cacheTags[$cacheTag])) {
        foreach ($cacheTags[$cacheTag] as $cacheName => $_) {
          $cacheIdsToInvalidate[] = $cacheName;
        }
        $cacheTags[$cacheTag] = [];
      }
    }

    Drupal::cache($this->binKey)->deleteMultiple($cacheIdsToInvalidate);
    $tagsCacheBin->set('cache_tags', $cacheTags, CACHE::PERMANENT);
  }

  protected function exists(string $key): bool {
    $record = Drupal::cache($this->binKey)->get($key);
    return !empty($record);
  }

  protected function makeKey(string $key, CacheIntent $intent): string {
    return "{$this->binKey}_{$intent->value}:$key";
  }

  protected function getCacheDurationTimestamp(): int {
    return time() + $this->getCacheDuration();
  }
  public function getCacheBinKey(): string {
    return $this->binKey;
  }
  public function getCacheDuration(): int {
    return $this->duration;
  }

  public function setCacheBinKey(string $binKey): self {
    if (!empty($cacheBinKey)) $this->binKey = $cacheBinKey;
    return $this;
  }
  public function setCacheDuration(int $duration): self {
    if ($duration > 0) $this->duration = $duration;
    return $this;
  }
}
