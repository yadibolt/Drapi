<?php

namespace Drupal\drift_eleven\Core2\Content\Entity\Base;
class EntityBase {
  protected function unpackValues(array $values): self {
    foreach ($values as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }

    return $this;
  }
}
