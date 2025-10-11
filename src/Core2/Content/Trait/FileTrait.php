<?php

trait FileTrait {
  use PathTrait;

  protected function includeFile(string $filePath): mixed {
    if (!self::isFile($filePath)) return null;
    return include_once $filePath;
  }
  protected function isReadableFile(string $filePath): bool {
    return is_readable($filePath);
  }
  protected function isDotFile(string $fileName): bool {
    return $fileName === '.' || $fileName === '..';
  }
  protected function isWritableFile(string $fileName): bool {
    return is_writable($fileName);
  }
  protected function isPHPFile(string $filePath): bool {
    return pathinfo($filePath, PATHINFO_EXTENSION) === 'php' && self::isFile($filePath) && self::isReadableFile($filePath);
  }
  protected function existsFile(string $filePath): bool {
    return file_exists($filePath);
  }
  protected function getFileExtension(string $filePath): ?string {
    if (!self::isFile($filePath)) return null;
    return pathinfo($filePath, PATHINFO_EXTENSION);
  }
}
