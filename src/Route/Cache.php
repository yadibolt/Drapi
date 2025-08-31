<?php

namespace Drupal\pingvin\Route;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class Cache {
  public const int DURATION_DEFAULT = 3600;
  public const int DURATION_PERMANENT = -1;
  public const int DURATION_NONE = 0;

  public static function hit(Request &$request, array $cacheTags): array {
    $query = \Drupal::database()->select(pw8dr1_PROJECT_ID . '_cache', 'pc')
      ->fields('pc')
      ->condition('pc.cache_tag', $cacheTags, 'IN');
    $result = $query->execute()->fetchAssoc();

    if (!empty($result) && $result['expires_at'] === self::DURATION_PERMANENT || $result['expires_at'] > time()) {
      $request->headers->set('x-pingvin-cache', 'HIT');
      $date = date('D, d M Y H:i:s \G\M\T', $result['expires_at']);
      $request->headers->set('x-pingvin-cache-expires-at', $date);

      return json_decode($result['context'], true);
    }

    if (!empty($result) && $result['expires_at'] <= time()) {
      // Cache expired, delete it
      \Drupal::database()->delete(pw8dr1_PROJECT_ID . '_cache')
        ->condition('id', $result['id'])
        ->execute();
      $result = [];
      $request->headers->set('x-pingvin-cache', 'MISS');
      $date = date('D, d M Y H:i:s \G\M\T', time());
      $request->headers->set('x-pingvin-cache-expires-at', $date);
    }

    if (empty($result)) {
      $date = date('D, d M Y H:i:s \G\M\T', time());

      $request->headers->set('x-pingvin-cache', 'MISS');
      $request->headers->set('x-pingvin-cache-expires-at', $date);
    }

    return $result ?: [];
  }

  public static function create(string $cacheTag, array $context = [], int $duration = self::DURATION_PERMANENT): bool {
    $query = \Drupal::database()->select(pw8dr1_PROJECT_ID . '_cache', 'pc')
      ->fields('pc')
      ->condition('pc.cache_tag', $cacheTag);
    $result = $query->execute()->fetchAssoc();

    if (!empty($result) && $result['expires_at'] <= time()) {
      self::clearByTag($cacheTag);
    }

    $query = \Drupal::database()->insert(pw8dr1_PROJECT_ID . '_cache')
      ->fields([
        'cache_tag' => $cacheTag,
        'context' => json_encode($context),
        'expires_at' => time() + $duration,
        'created_at' => time(),
      ]);

    return $query->execute() > 0;
  }

  public static function clearByTag(string $cacheTag): void {
    \Drupal::database()->delete(pw8dr1_PROJECT_ID . '_cache')
      ->condition('cache_tag', $cacheTag)
      ->execute();
  }

  public static function clear(): void {
    \Drupal::database()->delete(pw8dr1_PROJECT_ID . '_cache')
      ->execute();
  }
}
