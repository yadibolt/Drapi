<?php

namespace Drupal\drift_eleven\Core\Field;

use Drupal\Core\Field\FieldItemListInterface;

class LinkField {
  public function getValue(FieldItemListInterface $field, bool $loadEntity = false): null|string|int|float|array {
    $fieldName = $field->getName();
    $values = $field->getValue();

    if (count($values) === 1) {
      if (!empty($values[0])) {
        $link = [
          'uri' => $values[0]['uri'] ?: null,
          'title' => $values[0]['title'] ?: null,
          'options' => $values[0]['options'] ?: [],
        ];

        return $this->formatValues($fieldName, [$link]);
      }
    }

    if (count($values) > 1) {
      $vals = [];
      foreach ($values as $value) {
        if (!empty($value)) {
          $link = [
            'uri' => $value['uri'] ?: null,
            'title' => $value['title'] ?: null,
            'options' => $value['options'] ?: [],
          ];
          $vals[] = $link;
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
