<?php

namespace Drupal\drapi\Core\Content\Entity\Base;

abstract class EntityBase {
  protected function unpackValues(array $values): self {
    foreach ($values as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }

    return $this;
  }
}
