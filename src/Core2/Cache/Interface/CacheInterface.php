<?php

namespace Drupal\drift_eleven\Core2\Cache\Interface;

use Drupal\drift_eleven\Core2\Cache\Base\CacheBase;
use Drupal\drift_eleven\Core2\Cache\Enum\CacheIntent;

interface CacheInterface {
  public function get(string $key, CacheIntent $intent): mixed;
  public function make(string $key, CacheIntent $intent, mixed $data, array $tags = []): bool;
  public function delete(string $key, CacheIntent $intent): void;
  public function flush(): void;
  public function invalidateTags(array $tags): void;
  public function getCacheBinKey(): string;
  public function getCacheDuration(): int;
  public function setCacheBinKey(string $binKey): CacheBase;
  public function setCacheDuration(int $duration): CacheBase;
}
