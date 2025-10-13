<?php

namespace Drupal\drift_eleven\Core\Content\Entity\Base;
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
