<?php

namespace Drupal\drift_eleven\Core2\Content\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drift_eleven\Core2\Content\Field\Base\FieldBase;
use Drupal\drift_eleven\Core2\Content\Field\Interface\FieldInterface;

class IntegerField extends FieldBase implements FieldInterface {

  public function __construct(FieldItemListInterface $field){
    parent::__construct($field);
  }

  public function getFieldValues(array $options = []): null|string|int|float|array {
    $this->handleOptions($options);

    $values = $this->getValues();

    if (count($values) === 1 && !empty($values[0]) && isset($values[0]['value'])) {
      return $this->flattenValues([(int)$values[0]['value']]);
    }

    if (count($values) > 1) {
      $arrayValues = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['value'])) {
          $arrayValues[] = (int)$value['value'];
        }
      }

      return $this->flattenValues($arrayValues);
    }

    return null;
  }
}
