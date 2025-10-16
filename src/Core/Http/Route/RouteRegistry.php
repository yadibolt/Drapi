<?php

namespace Drupal\drift_eleven\Core\Http\Route;

use DirectoryIterator;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteRegistryBase;

class RouteRegistry extends RouteRegistryBase {
  public static function make(string $directoryPath): self {
    return new self($directoryPath);
  }
  public function scanDirectory(): ?array {
    if (!$this->isReadableFile($this->directoryPath) || !$this->isDirectory($this->directoryPath)) return null;

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
        $enabled = $this->registry[ROUTE_NAME_PREFIX_DEFAULT . ':' . $route->getId()]['enabled'] ?: true;
        $route->setEnabled($enabled);
      }

      $this->registry[ROUTE_NAME_PREFIX_DEFAULT . ':' . $route->getId()] = $route;
    }

    return $this->registry;
  }
}
