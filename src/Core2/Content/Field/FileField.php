<?php

namespace Drupal\drift_eleven\Core2\Content\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drift_eleven\Core2\Content\Entity\FileEntity;
use Drupal\drift_eleven\Core2\Content\Field\Base\FieldBase;
use Drupal\drift_eleven\Core2\Content\Field\Interface\FieldInterface;
use Drupal\file\Entity\File;

class FileField extends FieldBase implements FieldInterface {
  public function __construct(FieldItemListInterface $field){
    parent::__construct($field);
  }
  public function getFieldValues(array $options = []): null|array|int {
    $this->handleOptions($options);

    $values = $this->getValues();

    $arrayValues = [];
    if (count($values) === 1 && !empty($values[0]) && isset($values[0]['target_id'])) {
      $arrayValues[] = (int)$values[0]['target_id'];
    }

    if (count($values) > 1) {
      foreach ($values as $value) {
        if (!empty($value) && isset($value['target_id'])) {
          $arrayValues[] = (int)$value['target_id'];
        }
      }
    }

    if ($this->getLoadEntities()) {
      $entities = $this->getEntityFields($arrayValues);
      return $this->flattenValues($entities);
    } else {
      return $this->flattenValues($arrayValues);
    }
  }

  protected function getEntityFields(array $ids): ?array {
    if (empty($ids)) return null;

    $loaderValues = $this->getEntityLoaderValues($ids);
    if (empty($loaderValues)) return null;

    $result = [];
    foreach ($loaderValues as $loaderValue) {
      $result[] = new FileEntity([
        'id' => $loaderValue->id(),
        'alt' => $loaderValue->hasField('alt') ? $loaderValue->get('alt')->value : null,
        'title' => $loaderValue->hasField('title') ? $loaderValue->get('title')->value : null,
        'width' => $loaderValue->hasField('width') ? $loaderValue->get('width')->value : null,
        'height' => $loaderValue->hasField('height') ? $loaderValue->get('height')->value : null,
        'description' => $loaderValue->hasField('description') ? $loaderValue->get('description')->value : null,
        'filename' => $loaderValue->getFilename(),
        'uri' => $loaderValue->getFileUri(),
        'url' => $loaderValue->createFileUrl(false),
        'filemime' => $loaderValue->getMimeType(),
        'filesize' => $loaderValue->getSize(),
        'status' => $loaderValue->isPermanent() ? 'permanent' : 'temporary',
        'created' => $loaderValue->getCreatedTime(),
        'changed' => $loaderValue->getChangedTime(),
      ]);
    }

    return $result;
  }

  protected function getEntityLoaderValues(array $ids): ?array {
    return File::loadMultiple($ids);
  }
}
