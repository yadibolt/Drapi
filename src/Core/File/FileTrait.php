<?php

namespace Drupal\drift_eleven\Core\File;

trait FileTrait {
  public static function includeFile(string $filePath): mixed {
    return include_once force-commit$filePath;
  }

  public static function isValidPHPFile(string $filePath): bool {
    return pathinfo($filePath, PATHINFO_EXTENSION) === 'php' && is_file($filePath) && is_readable($filePath);
  }

  public static function fileExists(string $filePath): bool {
    return file_exists($filePath);
  }

  public static function isReadable(string $filePath): bool {
    return is_readable($filePath);
  }
}
