<?php

namespace Drupal\drift_eleven\Core\Http\Route\Base;

use Drupal;
use Drupal\drift_eleven\Core\Content\Trait\FileTrait;

abstract class RouteRegistryBase {
  use FileTrait;

  protected string $directoryPath;
  protected array $registry;

  public function __construct(string $directoryPath, array &$registry = []) {
    $configuration = Drupal::configFactory()->getEditable(ROUTE_CONFIG_NAME_DEFAULT);

    $this->directoryPath = $directoryPath;
    $this->registry = $configuration->get('route_registry') ?: [];
  }

  public function getDirectoryPath(): string {
    return $this->directoryPath;
  }
  public function getRegistry(): array {
    return $this->registry;
  }

  public function setDirectoryPath(string $directoryPath): self {
    $this->directoryPath = $directoryPath;
    return $this;
  }
  public function setRegistry(array $registry): self {
    $this->registry = $registry;
    return $this;
  }
}
