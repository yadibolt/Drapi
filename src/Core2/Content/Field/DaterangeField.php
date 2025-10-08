<?php

namespace Drupal\drift_eleven\Core2\Content\Field;

use Drupal\drift_eleven\Core2\Content\Field\Base\FieldBase;
use Drupal\drift_eleven\Core2\Content\Field\Interface\FieldInterface;

class DaterangeField extends FieldBase implements FieldInterface {
  public function getFieldValues($options = []): null|string|int|float|array {
    $values = $this->getValues();

    if (count($values) === 1 && !empty($values[0]) && isset($values[0]['value'])) {
      $daterange = [
        'start' => $values[0]['value'] ?: null,
        'end' => $values[0]['end_value'] ?: null,
      ];

      return $this->flattenValues([$daterange]);
    }

    if (count($values) > 1) {
      $arrayValues = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['value'])) {
          $arrayValues[] = [
            'start' => $values[0]['value'] ?: null,
            'end' => $values[0]['end_value'] ?: null,
          ];
        }
      }

      return $this->flattenValues($arrayValues);
    }

    return null;
  }
}
