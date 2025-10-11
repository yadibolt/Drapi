<?php

namespace Drupal\drift_eleven\Core2\Content\Entity\Resolver;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\drift_eleven\Core2\Utility\Trait\SanitizerTrait;
use Exception;

class PathResolver {
  use SanitizerTrait;

  protected ?string $destination;

  public function __construct(string $destination) {
    $sanitized = $this->sanitizeURL($destination);
    if (!empty($sanitized)) {
      $this->destination = $sanitized;
    } else {
      $this->destination = null;
    }
  }

  public function resolve(): ?EntityInterface {
    if (empty($this->destination)) return null;

    $url = Url::fromUserInput($this->destination);
    if (!$url->isRouted() || empty($url->getRouteParameters())) return null;

    $parameters = $url->getRouteParameters();
    try {
      return Drupal::entityTypeManager()
        ->getStorage(array_key_first($parameters))
        ->load(reset($parameters));
    } catch (Exception $e) {
      return null;
    }
  }
}
