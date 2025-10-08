<?php

trait PathTrait {
  public static function isDirectory(string $directoryPath): bool {
    return is_dir($directoryPath);
  }
  public static function isFile(string $filePath): bool {
    return is_file($filePath);
  }
  public static function joinPaths(string ...$paths): string {
    return preg_replace('#/+#','/', join('/', $paths));
  }
  public static function getDirectoryName(string $path): string {
    return dirname($path);
  }
  public static function getBaseName(string $path): string {
    return basename($path);
  }
  public static function normalizePath(string $path): string {
    return realpath($path) ?: $path;
  }
  public static function getAbsolutePath(string $relativePath, string $basePath = __DIR__): string {
    return self::normalizePath(self::joinPaths($basePath, $relativePath));
  }
}
