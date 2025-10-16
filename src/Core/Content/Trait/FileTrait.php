<?php

namespace Drupal\drift_eleven\Core\Content\Trait;

use Drupal\drift_eleven\Core\Utility\Enum\LoggerIntent;
use Drupal\drift_eleven\Core\Utility\Logger;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

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
  /**
   * @return ReflectionAttribute[]|null
   */
  public static function getFileAttributes(string $filePath): ?array {
    if (file_exists($filePath) && !(pathinfo($filePath, PATHINFO_EXTENSION) === 'php' && is_file($filePath) && is_readable($filePath))) return null;

    $classNamespace = null;

    $content = file_get_contents($filePath);
    if (!$content) {
      Logger::l(
        level: LoggerIntent::CRITICAL, message: 'Could not read file at @filePath.', context: ['@filePath' => $filePath]
      ); return null;
    }

    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
      $classNamespace = trim($matches[1]);
    }

    if (!$classNamespace) return null;

    $fileName = pathinfo($filePath, PATHINFO_FILENAME);
    $classClassName = $classNamespace . '\\' . $fileName;

    include_once $filePath;

    if (!class_exists($classClassName, false)) {
      Logger::l(
        level: LoggerIntent::CRITICAL, message: 'Class @classClassName does not exist.', context: ['@classClassName' => $classClassName]
      ); return null;
    }

    try {
      $reflection = new ReflectionClass($classClassName);
      return $reflection->getAttributes();
    } catch (ReflectionException $e) {
      Logger::l(
        level: LoggerIntent::CRITICAL, message: 'Could not create ReflectionClass for @classClassName. @error', context: ['@classClassName' => $classClassName, '@error' => $e->getMessage()]
      ); return null;
    }
  }
}
