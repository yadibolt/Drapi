<?php

namespace Drupal\drift_eleven\Core\Field;

use Drupal\Core\Field\FieldItemListInterface;

class TextField {
  public function getValue(FieldItemListInterface $field, bool $loadEntity = false): ?array {
    $fieldName = $field->getName();
    $values = $field->getValue();

    if (count($values) === 1) {
      if (!empty($values[0])) {
        $textField = [
          'value' => $values[0]['value'] ?: null,
          'format' => $values[0]['format'] ?: null,
        ];
        return $this->formatValues($fieldName, [$textField]);
      }
    }

    if (count($values) > 1) {
      $vals = [];
      foreach ($values as $value) {
        if (!empty($value)) {
          $textField = [
            'value' => $value['value'] ?: null,
            'format' => $value['format'] ?: null,
          ];
          $vals[] = $textField;
        }
      }
      return $this->formatValues($fieldName, $vals);
    }

    return $this->formatValues($fieldName, [null]);
  }

  protected function formatValues(string $fieldName, array $values): array {
    if (count($values) === 1) {
      return [
        $fieldName => $values[0]
      ];
    }

    if (count($values) > 1) {
      return [
        $fieldName => array_map(fn($v) => $v, $values)
      ];
    }

    return [
      $fieldName => null
    ];
  }
}
