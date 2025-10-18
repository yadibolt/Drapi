<?php

namespace Drupal\drapi\Core\Cache\Interface;

use Drupal\drapi\Core\Cache\Base\CacheBase;
use Drupal\drapi\Core\Cache\Enum\CacheIntent;

interface CacheInterface {
  public function get(string $key, CacheIntent $intent): mixed;
  public function create(string $key, CacheIntent $intent, mixed $data, array $tags = []): bool;
  public static function make(): self;
  public function delete(string $key, CacheIntent $intent): void;
  public function flush(): void;
  public function invalidateTags(array $tags): void;
  public function getCacheBinKey(): string;
  public function getCacheDuration(): int;
  public function setCacheBinKey(string $binKey): CacheBase;
  public function setCacheDuration(int $duration): CacheBase;
}
