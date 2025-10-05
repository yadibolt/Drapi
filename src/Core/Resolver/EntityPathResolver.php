<?php

namespace Drupal\drift_eleven\Core\Resolver;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Exception;
use http\Exception\InvalidArgumentException;

class EntityPathResolver {

  protected string $destination = '';
  public function resolve(): ?EntityInterface
  {
    if (empty($this->destination)) {
      throw new InvalidArgumentException("Destination cannot be empty");
    }

    $url = Url::fromUserInput($this->destination);
    if (!$url->isRouted()) {
      return null;
    }

    $params = $url->getRouteParameters();
    if (empty($params)) {
      return null;
    }

    $entity = null;
    try {
      $entity = Drupal::entityTypeManager()->getStorage(array_key_first($params))->load(reset($params));
    } catch (Exception) {
      return null;
    }

    return $entity ?: null;
  }

  public function setDestination(string $destination, array $options = []): self {
    $this->destination = $destination;

    return $this;
  }
}
