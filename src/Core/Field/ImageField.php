<?php

namespace Drupal\drift_eleven\Core\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Entity\File;

class ImageField {
  public function getValue(FieldItemListInterface $field, bool $loadEntity = false): null|string|int|float|array {
    $fieldName = $field->getName();
    $values = $field->getValue();

    $vals = [];

    if (count($values) === 1) {
      if (!empty($values[0])) {
        $vals[] = (int)$values[0]['target_id'];
      }
    }

    if (count($values) > 1) {
      $vals = [];
      foreach ($values as $value) {
        if (!empty($value)) {
          $vals[] = (int)$value['target_id'];
        }
      }
    }

    $results = [];
    if ($loadEntity) {
      $files = File::loadMultiple($vals);
      foreach ($files as $file) {
        $record = array_find($values, fn($v) => $v['target_id'] == $file->id());

        $results[] = [
          'alt' => $record['alt'] ?? null,
          'title' => $record['title'] ?? null,
          'width' => $record['width'] ?? null,
          'height' => $record['height'] ?? null,
          'url' => $file->createFileUrl(),
          'id' => $file->id(),
          'filename' => $file->getFilename(),
          'filesize' => $file->getSize(),
          'filemime' => $file->getMimeType(),
          'created' => $file->getCreatedTime(),
          'changed' => $file->getChangedTime(),
          'uri' => $file->getFileUri(),
        ];
      }
    }

    return $this->formatValues($fieldName, [$loadEntity ? $results : $vals]);
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
