<?php

trait FileTrait {
  use PathTrait;

  public static function includeFile(string $filePath): mixed {
    if (!self::isFile($filePath)) return null;
    return include_once $filePath;
  }
  public static function isReadableFile(string $filePath): bool {
    return is_readable($filePath);
  }
  public static function isDotFile(string $fileName): bool {
    return $fileName === '.' || $fileName === '..';
  }
  public static function isWritableFile(string $fileName): bool {
    return is_writable($fileName);
  }
  public static function isPHPFile(string $filePath): bool {
    return pathinfo($filePath, PATHINFO_EXTENSION) === 'php' && self::isFile($filePath) && self::isReadableFile($filePath);
  }
  public static function existsFile(string $filePath): bool {
    return file_exists($filePath);
  }
  public static function getFileExtension(string $filePath): ?string {
    if (!self::isFile($filePath)) return null;
    return pathinfo($filePath, PATHINFO_EXTENSION);
  }
}
