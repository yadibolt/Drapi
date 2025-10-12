<?php

namespace Drupal\drift_eleven\Core2\Content\Trait;

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
  public static function getFileDocComment(string $filePath): ?string {
    if (!(pathinfo($filePath, PATHINFO_EXTENSION) === 'php' && is_file($filePath) && is_readable($filePath))) return null;

    $content = file_get_contents($filePath);
    if ($content === false) return null;

    // taken from https://www.php.net/manual/en/tokens.php
    $tokens = token_get_all($content);
    foreach ($tokens as $token) {
      if (is_array($token) && $token[0] === T_DOC_COMMENT) {
        return $token[1];
      }
    }

    return null;
  }
}
