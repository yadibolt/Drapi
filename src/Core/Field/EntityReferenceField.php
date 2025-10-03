<?php

namespace Drupal\drift_eleven\Core\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drift_eleven\Core\Resolver\FieldResolver;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

class EntityReferenceField {
  public function getValue(FieldItemListInterface $field, bool $loadEntity = false): ?array {
    $fieldName = $field->getName();
    $values = $field->getValue();
    $target_type = $field->getFieldDefinition()->getSetting('target_type');

    $vals = [];
    if (count($values) === 1) {
      if (!empty($values[0]) && isset($values[0]['target_id'])) {
        $vals[] = (int)$values[0]['target_id'];
      }
    }

    if (count($values) > 1) {
      $vals = [];
      foreach ($values as $value) {
        if (!empty($value) && isset($value['target_id'])) {
          $vals[] = (int)$value['target_id'];
        }
      }
    }

    if ($loadEntity) {
      $entities[] = match($target_type) {
        'node' => $this->getAssociativeNodeFields($vals),
        'user' => $this->getAssociativeUserFields($vals),
        'taxonomy_term' => $this->getAssociativeTaxonomyFields($vals),
        'file' => $this->getAssociativeFileFields($vals),
        'media' => $this->getAssociativeMediaFields($vals),
        default => [],
      };
    }

    return $this->formatValues($fieldName, $loadEntity ? $entities ?: [] : $vals);
  }

  protected function formatValues(string $fieldName, array $values): array {
    if (count($values) === 1) {
      return [
        $fieldName => $values[0]
      ];
    }

    if (count($values) > 1) {
      return [
        $fieldName => $values
      ];
    }

    return [
      $fieldName => null
    ];
  }

  protected function getAssociativeNodeFields(array $nodeIds): array {
    $nodes = Node::loadMultiple($nodeIds);
    $result = [];
    foreach ($nodes as $node) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($node->getFields())->resolveFields(true);

      $result[] = $resolvedFields;
    }

    return $result;
  }

  protected function getAssociativeUserFields(array $userIds): array {
    $users = User::loadMultiple($userIds);
    $result = [];
    foreach ($users as $user) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($user->getFields())->resolveFields(true);

      $result[] = $resolvedFields;
    }

    return $result;
  }

  protected function getAssociativeTaxonomyFields(array $taxonomyIds): array {
    $terms = Term::loadMultiple($taxonomyIds);
    $result = [];
    foreach ($terms as $term) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($term->getFields())->resolveFields(true);

      $result[] = $resolvedFields;
    }

    return $result;
  }

  protected function getAssociativeMediaFields(array $mediaIds): array {
    $medias = Media::loadMultiple($mediaIds);
    $result = [];
    foreach ($medias as $media) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($media->getFields())->resolveFields(true);

      $result[] = $resolvedFields;
    }

    return $result;
  }

  protected function getAssociativeFileFields(array $fileIds): array {
    $files = File::loadMultiple($fileIds);
    $result = [];
    foreach ($files as $file) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($file->getFields())->resolveFields(true);

      $result[] = $resolvedFields;
    }

    return $result;
  }
}
