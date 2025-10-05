<?php

namespace Drupal\drift_eleven\Core\Field;

use Drupal\Core\Field\FieldItemListInterface;

class PathField {
  public function getValue(FieldItemListInterface $field, bool $loadEntity = false): null|string|int|float|array {
    $fieldName = $field->getName();
    $values = $field->getValue();

    if (count($values) === 1) {
      if (!empty($values[0])) {
        $path = [
          'alias' => $values[0]['alias'] ?: null,
          'pid' => $values[0]['pid'] ?: null,
          'langcode' => $values[0]['langcode'] ?: null,
        ];
        return $this->formatValues($fieldName, [$path]);
      }
    }

    if (count($values) > 1) {
      $vals = [];
      foreach ($values as $value) {
        if (!empty($value)) {
          $path = [
            'alias' => $value['alias'] ?: null,
            'pid' => $value['pid'] ?: null,
            'langcode' => $value['langcode'] ?: null,
          ];
          $vals[] = $path;
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
