<?php

namespace Drupal\drift_eleven\Core\Field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drift_eleven\Core\Resolver\FieldResolver;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use InvalidArgumentException;

class EntityReferenceField extends FieldFoundation {
  protected bool $customFieldsOnly = FieldInterface::CUSTOM_FIELDS_ONLY;
  protected bool $loadEntities = FieldInterface::LOAD_ENTITIES;
  protected bool $includeProtectedFields = FieldInterface::INCLUDE_PROTECTED_FIELDS;

  public function getValue(FieldItemListInterface $field, array $options = []): null|string|int|float|array {
    if (!empty($options)) {
      if (isset($options['customFieldsOnly']) && is_bool($options['customFieldsOnly'])) {
        $this->customFieldsOnly = $options['customFieldsOnly'];
      } else {
        throw new InvalidArgumentException('The "customFieldsOnly" option must be a boolean.');
      }

      if (isset($options['includeProtectedFields']) && is_bool($options['includeProtectedFields'])) {
        $this->includeProtectedFields = $options['includeProtectedFields'];
      } else {
        throw new InvalidArgumentException('The "includeProtectedFields" option must be a boolean.');
      }

      if (isset($options['loadEntities']) && is_bool($options['loadEntities'])) {
        $this->loadEntities = $options['loadEntities'];
      } else {
        throw new InvalidArgumentException('The "loadEntities" option must be a boolean.');
      }
    }

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

    if ($this->loadEntities) {
      $entities[] = match($target_type) {
        'node' => $this->getAssociativeNodeFields($vals),
        'user' => $this->getAssociativeUserFields($vals),
        'taxonomy_term' => $this->getAssociativeTaxonomyFields($vals),
        'file' => $this->getAssociativeFileFields($vals),
        'media' => $this->getAssociativeMediaFields($vals),
        default => [],
      };
    }

    return $this->formatValues($fieldName, $this->loadEntities ? $entities ?: [] : $vals);
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

  protected function getAssociativeNodeFields(array $nodeIds): array {
    $nodes = Node::loadMultiple($nodeIds);
    $result = [];
    foreach ($nodes as $node) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($node->getFields(), [
        'customFieldsOnly' => $this->customFieldsOnly,
        'includeProtectedFields' => $this->includeProtectedFields,
        'loadEntities' => $this->loadEntities,
      ])->resolveFields();

      $result[] = $resolvedFields;
    }

    return $result;
  }

  protected function getAssociativeUserFields(array $userIds): array {
    $users = User::loadMultiple($userIds);
    $result = [];
    foreach ($users as $user) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($user->getFields(), [
        'customFieldsOnly' => $this->customFieldsOnly,
        'includeProtectedFields' => $this->includeProtectedFields,
        'loadEntities' => $this->loadEntities,
      ])->resolveFields();

      $result[] = $resolvedFields;
    }

    return $result;
  }

  protected function getAssociativeTaxonomyFields(array $taxonomyIds): array {
    $terms = Term::loadMultiple($taxonomyIds);
    $result = [];
    foreach ($terms as $term) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($term->getFields(), [
        'customFieldsOnly' => $this->customFieldsOnly,
        'includeProtectedFields' => $this->includeProtectedFields,
        'loadEntities' => $this->loadEntities,
      ])->resolveFields();

      $result[] = $resolvedFields;
    }

    return $result;
  }

  protected function getAssociativeMediaFields(array $mediaIds): array {
    $medias = Media::loadMultiple($mediaIds);
    $result = [];
    foreach ($medias as $media) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($media->getFields(), [
        'customFieldsOnly' => $this->customFieldsOnly,
        'includeProtectedFields' => $this->includeProtectedFields,
        'loadEntities' => $this->loadEntities,
      ])->resolveFields();

      $result[] = $resolvedFields;
    }

    return $result;
  }

  protected function getAssociativeFileFields(array $fileIds): array {
    $files = File::loadMultiple($fileIds);
    $result = [];
    foreach ($files as $file) {
      $fieldResolver = new FieldResolver();
      $resolvedFields = $fieldResolver->setFields($file->getFields(), [
        'customFieldsOnly' => $this->customFieldsOnly,
        'includeProtectedFields' => $this->includeProtectedFields,
        'loadEntities' => $this->loadEntities,
      ])->resolveFields();

      $result[] = $resolvedFields;
    }

    return $result;
  }
}
