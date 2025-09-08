<?php

namespace Drupal\drift_eleven\Core\Route;

use DirectoryIterator;
use Drupal\drift_eleven\Core\File\FileTrait;
use Exception;

class RouteRegistry implements RouteRegistryInterface {
    use FileTrait;

    /**
     * @param string $directoryPath
     * @param array $registry
     * @return array|null array of Route objects or null
     */
    public static function scanDir(string $directoryPath, array &$registry = []): ?array {
        if (!self::isReadable($directoryPath) || !self::isDir($directoryPath)) return null;

        foreach (new DirectoryIterator($directoryPath) as $file) {
            $filePath = realpath($file->getPathname());

            // handle subdirectories
            if ($file->isDir() && !$file->isDot()) {
                if (self::isReadable($filePath)) {
                    self::scanDir($filePath);
                }
            }

            if (!self::isValidPHPFile($filePath)) continue;

            // handle files
            try {
                $route = RouteBuilder::build($file); // build the actual route object

                // skip routes that do not pass assertions
                if (!$route->applyAssertions()) continue;

                $registry[] = $route;
            } catch (Exception) {}
        }

        return $registry;
    }
}
