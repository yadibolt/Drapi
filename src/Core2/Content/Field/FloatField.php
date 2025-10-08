<?php

namespace Drupal\drift_eleven\Core2\Content\Field;

use Drupal\drift_eleven\Core2\Content\Field\Base\FieldBase;
use Drupal\drift_eleven\Core2\Content\Field\Interface\FieldInterface;

class FloatField extends FieldBase implements FieldInterface {
  public function getFieldValues($options = []): null|string|int|float|array {
    $values = $this->getValues();

    if (count($values) === 1 && !empty($values[0]) && isset($values[0]['value'])) {
      return $this->flattenValues([(float)$values[0]['value']]);
    }

    if (count($values) > 1) {
      $arrayValues = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['value'])) {
          $arrayValues[] = (float)$value['value'];
        }
      }

      return $this->flattenValues($arrayValues);
    }

    return null;
  }
}
