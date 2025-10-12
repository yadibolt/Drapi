<?php

namespace Drupal\drift_eleven\Core\Content\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drift_eleven\Core\Content\Field\Base\FieldBase;
use Drupal\drift_eleven\Core\Content\Field\Interface\FieldInterface;

class PathField extends FieldBase implements FieldInterface {
  public function __construct(FieldItemListInterface $field){
    parent::__construct($field);
  }
  public function getFieldValues(array $options = []): ?array {
    $this->handleOptions($options);

    $values = $this->getValues();

    if (count($values) === 1 && !empty($values[0])) {
      $path = [
        'alias' => $values[0]['alias'] ?? null,
        'pid' => $values[0]['pid'] ?? null,
        'langcode' => $values[0]['langcode'] ?? null,
      ];

      return $this->flattenValues([$path]);
    }

    if (count($values) > 1) {
      $arrayValues = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['value'])) {
          $arrayValues[] = [
            'alias' => $values[0]['alias'] ?? null,
            'pid' => $values[0]['pid'] ?? null,
            'langcode' => $values[0]['langcode'] ?? null,
          ];
        }
      }

      return $this->flattenValues($arrayValues);
    }

    return null;
  }
}
