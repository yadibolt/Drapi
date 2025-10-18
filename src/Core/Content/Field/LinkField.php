<?php

namespace Drupal\drapi\Core\Content\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drapi\Core\Content\Field\Base\FieldBase;
use Drupal\drapi\Core\Content\Field\Interface\FieldInterface;

class LinkField extends FieldBase implements FieldInterface {
  public function __construct(FieldItemListInterface $field){
    parent::__construct($field);
  }
  public function getFieldValues(array $options = []): ?array {
    $this->handleOptions($options);

    $values = $this->getValues();

    if (count($values) === 1 && !empty($values[0])) {
      $link = [
        'uri' => $values[0]['uri'] ?? null,
        'title' => $values[0]['title'] ?? null,
        'options' => $values[0]['options'] ?? [],
      ];

      return $this->flattenValues([$link]);
    }

    if (count($values) > 1) {
      $arrayValues = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['value'])) {
          $arrayValues[] = [
            'uri' => $values[0]['uri'] ?? null,
            'title' => $values[0]['title'] ?? null,
            'options' => $values[0]['options'] ?? [],
          ];
        }
      }

      return $this->flattenValues($arrayValues);
    }

    return null;
  }
}
