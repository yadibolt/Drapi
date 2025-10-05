<?php

namespace Drupal\drift_eleven\Core\Field;

use Drupal\Core\Field\FieldItemListInterface;

class FloatField {
  public function getValue(FieldItemListInterface $field, bool $loadEntity = false): null|string|int|float|array {
    $fieldName = $field->getName();
    $values = $field->getValue();

    if (count($values) === 1) {
      if (!empty($values[0]) && isset($values[0]['value'])) {
        return $this->formatValues($fieldName, [(float)$values[0]['value']]);
      }
    }

    if (count($values) > 1) {
      $vals = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['value'])) {
          $vals[] = (float)$value['value'];
        }
      }
      return $this->formatValues($fieldName, $vals);
    }

    return $this->formatValues($fieldName, [null]);
  }

  protected function formatValues(string $fieldName, array $values): null|string|int|float|array {
    if (count($values) === 1) {
      return $values[0];
    }

    if (count($values) > 1) {
      return array_map(fn($v) => $v, $values);
    }

    return null;
  }
}
