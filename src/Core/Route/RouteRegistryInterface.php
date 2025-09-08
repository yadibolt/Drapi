<?php

namespace Drupal\drift_eleven\Core\Route;

interface RouteRegistryInterface {
    /**
     * Scans a directory for route definition files and builds an array of Route objects.
     *
     * @param string $directoryPath path to the directory to scan
     * @return array|null array of Route objects or null if the directory is not readable or does not exist
     */
    public static function scanDir(string $directoryPath): ?array;
}
