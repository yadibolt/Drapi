<?php

namespace Drupal\drift_eleven\Core2\Content\Field;

use Drupal\drift_eleven\Core2\Content\Field\Base\FieldBase;
use Drupal\drift_eleven\Core2\Content\Field\Interface\FieldInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

class EntityReferenceField extends FieldBase implements FieldInterface {
  public function getFieldValues($options = []): null|string|int|float|array {
    $this->handleOptions($options);

    $values = $this->getValues();
    $targetType = $this->field->getFieldDefinition()->getType();

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
      $entities[] = $this->getEntityFields($targetType, $arrayValues);
      return $this->flattenValues($entities);
    } else {
      return $this->flattenValues($arrayValues);
    }
  }

  protected function getEntityFields(string $entityType, array $ids): array {
    $loaderValues = $this->getEntityLoaderValues($entityType, $ids);

    $result = [];
    foreach ($loaderValues as $loaderValue) {
      $result[] = new FieldResolver()->setFields($loaderValue->getFields(), [
        'load_entities' => $this->getLoadEntities(),
        'load_custom' => $this->getLoadCustom(),
        'load_protected' => $this->getLoadProtected(),
      ])->resolve();
    }

    return $result;
  }

  protected function getEntityLoaderValues(string $entityType, $ids): array {
    return match ($entityType) {
      'node' => Node::loadMultiple($ids),
      'user' => User::loadMultiple($ids),
      'taxonomy_term' => Term::loadMultiple($ids),
      'file' => File::loadMultiple($ids),
      'media' => Media::loadMultiple($ids),
    };
  }
}
