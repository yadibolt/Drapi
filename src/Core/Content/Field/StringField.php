<?php

namespace Drupal\drapi\Core\Content\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drapi\Core\Content\Field\Base\FieldBase;
use Drupal\drapi\Core\Content\Field\Interface\FieldInterface;

class StringField extends FieldBase implements FieldInterface {

  public function __construct(FieldItemListInterface $field){
    parent::__construct($field);
  }

  public function getFieldValues(array $options = []): null|array|string {
    $this->handleOptions($options);

    $values = $this->getValues();

    if (count($values) === 1 && !empty($values[0]) && isset($values[0]['value'])) {
      return $this->flattenValues([(string)$values[0]['value']]);
    }

    if (count($values) > 1) {
      $arrayValues = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['value'])) {
          $arrayValues[] = (string)$value['value'];
        }
      }

      return $this->flattenValues($arrayValues);
    }

    return null;
  }
}
