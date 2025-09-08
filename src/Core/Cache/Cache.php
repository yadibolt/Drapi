<?php

namespace Drupal\drift_eleven\Core\Cache;

use Drupal;
use stdClass;

class Cache implements CacheInterface {
  public static function find(string $key): ?array {
    $record = Drupal::cache('drift_eleven')->get($key);
    return !empty($record) ? self::format($record) : null;
  }

  public static function make(string $key, mixed $data, int $cacheDuration = self::DURATION_DEFAULT): void {
    if (self::find($key)) return;
    Drupal::cache('drift_eleven')->set($key, $data, time() + $cacheDuration);
  }

  public static function invalidate(string $key): void {
    if (self::find($key)) {
      Drupal::cache('drift_eleven')->invalidate($key);
    }
  }

  public static function format(stdClass $cacheRecord): ?array {
    return !empty($cacheRecord) && !empty($cacheRecord->data) ? $cacheRecord->data : null;
  }
}
