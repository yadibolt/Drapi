<?php

namespace Drupal\drift_eleven\Core\Cache;

use stdClass;

interface CacheInterface {
  /**
   * Cache key used in module
   * @var string
   */
  public const string CACHE_KEY = D9M7_CACHE_KEY;
  /**
   * Cache bin key used in module
   * @var string
   */
  public const string CACHE_BIN_KEY = D9M7_CACHE_BIN_KEY;
  /**
   * Default TTL for cache
   * @var int
   */
  public const int DURATION_DEFAULT = 3600;
  /**
   * No duration TTL for cache
   * @var int
   */
  public const int DURATION_NONE = 0;
  /**
   * Permanent TTL for cache
   * @var int
   */
  public const int DURATION_PERMANENT = -1;

  /**
   * Tries to find a cache record using \Drupal::cache() method
   *
   * @param string $key - key of cache record
   * @return array|null - returns array if the cache record is found, else null
   */
  public static function find(string $key): ?array;

  /**
   * Creates a new cache record
   *
   * @param string $key - key of cache record
   * @param mixed $data - data to store
   * @param int $cacheDuration
   * @return void - returns bool respectively
   */
  public static function make(string $key, mixed $data, int $cacheDuration = self::DURATION_DEFAULT): void;

  /**
   * Invalidates existing cache record. If none found, exits silently
   *
   * @param string $key - key of cache record
   * @return void
   */
  public static function invalidate(string $key): void;

  /**
   * Formats existing cache \stdClass object as an associative array
   *
   * @return array|null - returns formatted array on success, else null
   */
  public static function format(stdClass $cacheRecord): ?array;
}
