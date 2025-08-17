<?php

namespace Drupal\pingvin\Registry;

use DirectoryIterator;
use Drupal\pingvin\Route\RouteFile;
use Exception;

class RouteRegistry {
  /**
   * The registry of routes.
   * Contains all viable route file paths
   * file class Doc Comment
   *
   * @var array
   */
  protected array $routePathRegistry;
  /**
   * The directory path where route files are stored.
   * This is the root directory for subdirectory lookup.
   *
   * @var string
   */
  private string $routesDirectoryPath;

  /**
   * Constructs a new RouteRegistry instance.
   *
   * @param string $routesDirectoryPath
   *    The directory where route files are stored.
   *    We do subdirectory lookup, so this should be a root directory.
   */
  public function __construct(string $routesDirectoryPath) {
    $this->routesDirectoryPath = $routesDirectoryPath;
  }

  /**
   * Registers route files from a given directory.
   *
   * @param string $directoryPath
   *    The directory to scan for route files. If empty, uses the value from constructor.
   *    This parameter exists exclusively because of recursive calls
   *
   * @return array
   *    Returns an array of registered route file paths.
   *
   * @throws Exception
   *    If the provided directory does not exist or is not readable,
   *    or if a route file is invalid. (All files that are not PHP files are ignored.)
   */
  public function registerRoutes(string $directoryPath = ''): array {
    if (empty($this->routePathRegistry)) $this->routePathRegistry = [];
    if (empty($directoryPath)) $directoryPath = $this->routesDirectoryPath;

    if (!is_dir($directoryPath)) {
      throw new Exception('The provided directory does not exist: ' . $directoryPath);
    }

    if (!is_readable($directoryPath)) {
      throw new Exception('The provided directory is not readable: ' . $directoryPath);
    }

    foreach (new DirectoryIterator($directoryPath) as $file) {
      $filePath = realpath($file->getPathname());

      // subdirectories
      if ($file->isDir() && !$file->isDot()) {
        if ($file->isReadable()) {
          $this->registerRoutes($filePath);
        }
      }

      // files
      if ($file->isFile() && !$file->isDot()) {
        if ($file->getExtension() !== 'php') {
          continue;
        }

        $routeFile = new RouteFile($filePath);
        try {
          if (!$routeFile->isValid()) {
            throw new Exception('Invalid route file: ' . $filePath);
          }
        } catch (Exception $e) {
          throw new Exception('Error validating route file: ' . $filePath . "\n" . $e->getMessage());
        }

        $this->routePathRegistry[] = $filePath;
      }
    }

    return $this->routePathRegistry ?: [];
  }
}
