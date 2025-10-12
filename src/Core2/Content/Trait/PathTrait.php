<?php

namespace Drupal\drift_eleven\Core2\Content\Trait;
trait PathTrait {
  protected function isDirectory(string $directoryPath): bool {
    return is_dir($directoryPath);
  }
  protected function isFile(string $filePath): bool {
    return is_file($filePath);
  }
  protected function joinPaths(string ...$paths): string {
    return preg_replace('#/+#','/', join('/', $paths));
  }
  protected function getDirectoryName(string $path): string {
    return dirname($path);
  }
  protected function getBaseName(string $path): string {
    return basename($path);
  }
  protected function normalizePath(string $path): string {
    return realpath($path) ?? $path;
  }
  protected function getAbsolutePath(string $relativePath, string $basePath = __DIR__): string {
    return self::normalizePath(self::joinPaths($basePath, $relativePath));
  }
}
