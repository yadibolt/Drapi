<?php

namespace Drupal\drift_eleven\Core2\Http\Route;

use DirectoryIterator;
use Drupal\drift_eleven\Core2\Http\Route\Base\RouteRegistryBase;

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

      $route = Route::fromDocComment($filePath);
      if ($route === null) continue;

      if (isset($this->registry[$route->getId()])) {
        $enabled = $this->registry[$route->getId()]['enabled'] ?: true;
        $route->setEnabled($enabled);
      }

      $this->registry[] = $route;
    }

    return $this->registry;
  }
}
