<?php

namespace Drupal\drift_eleven\Core\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Entity\File;

class FileField {
  public function getValue(FieldItemListInterface $field, bool $loadEntity = false): ?array {
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
        $results[] = [
          'id' => $file->id(),
          'url' => $file->createFileUrl(false),
          'title' => $file->getFilename(),
          'filename' => $file->getFilename(),
          'filesize' => $file->getSize(),
          'filemime' => $file->getMimeType(),
          'uri' => $file->getFileUri(),
          'status' => $file->isPermanent() ? 'permanent' : 'temporary',
          'created' => $file->getCreatedTime(),
          'changed' => $file->getChangedTime(),
        ];
      }
    }

    return $this->formatValues($fieldName, [$loadEntity ? $results : $vals]);
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
