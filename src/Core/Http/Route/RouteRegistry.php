<?php

namespace Drupal\drapi\Core\Http\Route;

use DirectoryIterator;
use Drupal\drapi\Core\Http\Route\Base\RouteRegistryBase;

class RouteRegistry extends RouteRegistryBase {
  public static function make(string $directoryPath): self {
    return new self($directoryPath);
  }
  public function scanDirectory(): array {
    if (!$this->isReadableFile($this->directoryPath) || !$this->isDirectory($this->directoryPath)) return [];

    foreach (new DirectoryIterator($this->directoryPath) as $file) {
      $filePath = realpath($file->getPathname());

      if ($file->isDot()) continue;
      if (!$this->isReadableFile($filePath)) continue;
      if (!$file->isFile() && !$file->isDir()) continue;

      if ($file->isDir()) {
        $this->setDirectoryPath($filePath);
        $this->scanDirectory();
        continue;
      }

      if (!$this->isPHPFile($filePath)) continue;

      $route = Route::fromAttributes($filePath);
      if ($route === null) continue;

      if (isset($this->registry[ROUTE_NAME_PREFIX_DEFAULT . ':' . $route->getId()])) {
        /** @var Route $routeDef */
        $routeRef = $this->registry[ROUTE_NAME_PREFIX_DEFAULT . ':' . $route->getId()];
        $routeRef = unserialize($routeRef);
        $routeEnabled = $routeRef->isEnabled() ?? false;
        $route->setEnabled($routeEnabled);
      }

      $this->registry[ROUTE_NAME_PREFIX_DEFAULT . ':' . $route->getId()] = serialize($route);
    }

    return $this->registry;
  }
  public function scanDirectories(string $rootDirectoryName): array {
    $directoryPaths = $this->findMultipleDirectoryPaths($rootDirectoryName);

    foreach ($directoryPaths as $directoryPath) {
      $this->setDirectoryPath($directoryPath);
      $this->scanDirectory();
    }

    return $this->registry;
  }
  public function findMultipleDirectoryPaths(string $directoryName, &$directoryPaths = []): ?array {
    foreach (new DirectoryIterator($this->directoryPath) as $dir) {
      $directoryPath = realpath($dir->getPathname());

      if ($dir->isDot()) continue;
      if (!$dir->isDir()) continue;
      if ($dir->getFilename() === MODULE_FOLDER_NAME) continue;

      if ($dir->getFilename() === $directoryName && $this->isReadableFile($directoryPath)) {
        $routesDirPath = $directoryPath . '\\Routes';
        if ($this->isDirectory($routesDirPath) && $this->isReadableFile($routesDirPath)) {
          $directoryPaths[] = $routesDirPath;
        }

      } else {
        $this->setDirectoryPath($directoryPath);
        $subDirectoryPaths = $this->findMultipleDirectoryPaths($directoryName);
        if (is_array($subDirectoryPaths) && !empty($subDirectoryPaths)) {
          $directoryPaths = array_merge($directoryPaths, $subDirectoryPaths);
        }
      }
    }

    return !empty($directoryPaths) ? $directoryPaths : null;
  }
}
