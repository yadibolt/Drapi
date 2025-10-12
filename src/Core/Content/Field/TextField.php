<?php

namespace Drupal\drift_eleven\Core\Content\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drift_eleven\Core\Content\Field\Base\FieldBase;
use Drupal\drift_eleven\Core\Content\Field\Interface\FieldInterface;

class TextField extends FieldBase implements FieldInterface {
  public function __construct(FieldItemListInterface $field){
    parent::__construct($field);
  }
  public function getFieldValues(array $options = []): ?array {
    $this->handleOptions($options);

    $values = $this->getValues();

    if (count($values) === 1 && !empty($values[0])) {
      $textField = [
        'value' => (isset($values[0]['value']) ? $values[0]['value'] : null),
        'summary' => (isset($values[0]['summary']) ? $values[0]['summary'] : null),
        'format' => (isset($values[0]['format']) ? $values[0]['format'] : null),
      ];

      return $this->flattenValues([$textField]);
    }

    if (count($values) > 1) {
      $arrayValues = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['value'])) {
          $arrayValues[] = [
            'value' => (isset($value['value']) ? $value['value'] : null),
            'summary' => (isset($value['summary']) ? $value['summary'] : null),
            'format' => (isset($value['format']) ? $value['format'] : null),
          ];
        }
      }

      return $this->flattenValues($arrayValues);
    }

    return null;
  }
}
