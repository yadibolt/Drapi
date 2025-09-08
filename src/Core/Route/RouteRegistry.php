<?php

namespace Drupal\drift_eleven\Core\Route;

use DirectoryIterator;
use Drupal\drift_eleven\Core\File\FileTrait;
use Drupal\drift_eleven\Core\Logger\Logger;
use Exception;

class RouteRegistry implements RouteRegistryInterface {
    use FileTrait;

    public static function scanDir(string $directoryPath, array &$registry = []): ?array {
        if (!self::isReadable($directoryPath) || !self::isDir($directoryPath)) return null;

        foreach (new DirectoryIterator($directoryPath) as $file) {
          $filePath = realpath($file->getPathname());

          if ($file->isDot()) continue;
          if (!self::isReadable($filePath)) continue;
          if (!$file->isFile() && !$file->isDir()) continue;

          if ($file->isDir()) {
            self::scanDir($filePath, $registry);
            continue;
          }

          if (!self::isValidPHPFile($filePath)) continue;

          try {
            $route = RouteBuilder::build($filePath); // build the actual route object
            // skip routes that do not pass assertions
            if (!$route->applyAssertions()) continue;

            $registry[] = $route;
          } catch (Exception) {
            Logger::l('Failed to register route from file: @filePath', ['@filePath' => $filePath], 'error');
            continue;
          }
        }

        return $registry;
    }
}
