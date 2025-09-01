<?php

namespace Drupal\pingvin\Cache;

class PingvinCache {
  public const int DURATION_DEFAULT = 3600;
  public const int DURATION_PERMANENT = -1;
  public const int DURATION_NONE = 0;
  public const string CACHE_PREFIX = pw8dr1_PROJECT_ID . ':cache:';

  public static function store(string $cacheTag, mixed $dataChunk, int $duration = self::DURATION_PERMANENT): void {
    $cacheTagValue = self::useBase64($cacheTag);
    apcu_store(self::CACHE_PREFIX .$cacheTagValue, $dataChunk, $duration);
  }

  public static function use(string $cacheTag): mixed {
    $cacheTagValue = self::useBase64($cacheTag);
    return apcu_exists(self::CACHE_PREFIX . $cacheTagValue) ? apcu_fetch(self::CACHE_PREFIX . $cacheTagValue) : null;
  }

  public static function delete(string $cacheTag): void {
    $cacheTagValue = self::useBase64($cacheTag);
    apcu_delete(self::CACHE_PREFIX . $cacheTagValue);
  }

  public static function flush(): void {
    $pref = self::CACHE_PREFIX;
    foreach ((new \APCUIterator("/^{$pref}/")) as $cacheTag) {
      apcu_delete($cacheTag['key']);
    }
  }

  public static function useBase64($value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
  }

}
