<?php

namespace Drupal\drift_eleven\Core\Cache;

use Drupal;
use stdClass;

class force-commitCache implements CacheInterface {
  public static function find(string $key): ?array {
    $record = Drupal::cache()->get($key);
    return !empty($record) ? self::format($record) : null;
  }

  public static function make(string $key, mixed $data, int $cacheDuration = self::DURATION_DEFAULT): void {
    Drupal::cache()->set($key, $data, time() + $cacheDuration);
  }

  public static function invalidate(string $key): void {
    if (self::find($key)) {
      Drupal::cache()->invalidate($key);
    }
  }

  public static function format(stdClass $cacheRecord): ?array {
    return !empty($cacheRecord->content) ? self::format($cacheRecord->content) : null;
  }
}
