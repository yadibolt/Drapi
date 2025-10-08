<?php

namespace Drupal\drift_eleven\Core2\Content\Field;

class FieldResolver {
  protected const array FIELD_TYPE_HANDLERS = [
    'boolean' => BooleanField::class,
  ];
  protected array $fields = [];
  protected bool $loadCustom;
  protected bool $loadEntities;
  protected bool $loadProtected;

  public function __construct() {
    $this->loadEntities = false;
    $this->loadCustom = true;
    $this->loadProtected = false;
  }

  public function resolve(): array {
    if (empty($this->fields)) return [];

    $resolved = [];
    foreach ($this->fields as $fieldName => $field) {
      $definition = $field->getDefinition();
      $type = $definition->getType();

      if (in_array($fieldName, $this->getBaseFieldNames()) && $this->loadCustom) continue;
      if (in_array($fieldName, $this->getProtectedFieldNames()) && !$this->loadProtected) continue;

      if (!isset(self::FIELD_TYPE_HANDLERS[$type])) continue;

      $handler = self::FIELD_TYPE_HANDLERS[$type];
      if ($handler === null) {
        $resolved[$fieldName] = null;
        continue;
      }

      if ($handler === EntityReferenceField::class) {
        $resolved[$fieldName] = new $handler($field)->getFieldValues([
          'load_entities' => $this->getLoadEntities(),
          'load_custom' => $this->getLoadCustom(),
          'load_protected' => $this->getLoadProtected(),
        ]);
      } else {
        $resolved[$fieldName] = new $handler($field)->getFieldValues();
      }
    }

    return $resolved;
  }

  protected function handleOptions(array $options): self {
    if (isset($options['load_entities']) && is_bool($options['load_entities'])) {
      $this->setLoadEntities($options['load_entities']);
    }
    if (isset($options['load_custom']) && is_bool($options['load_custom'])) {
      $this->setLoadCustom($options['load_custom']);
    }
    if (isset($options['load_protected']) && is_bool($options['load_protected'])) {
      $this->setLoadProtected($options['load_protected']);
    }

    return $this;
  }

  public function setFields(array $fields, array $options = []): self {
    $this->handleOptions($options);
    $this->fields = $fields;
    return $this;
  }
  public function setLoadEntities(bool $loadEntities): self {
    $this->loadEntities = $loadEntities;
    return $this;
  }
  public function setLoadCustom(bool $loadCustom): self {
    $this->loadCustom = $loadCustom;
    return $this;
  }
  public function setLoadProtected(bool $loadProtected): self {
    $this->loadProtected = $loadProtected;
    return $this;
  }

  public function getBaseFieldNames(): array {
    return [
      'nid',
      'vid',
      'type',
      'langcode',
      'status',
      'promote',
      'sticky',
      'created',
      'changed',
      'path',
    ];
  }
  public function getProtectedFieldNames(): array {
    return [
      'uid',
      'uuid',
      'comment',
      'revision_id',
      'revision_user',
      'content_translation_created',
      'reusable',
      'revision_default',
      'content_translation_source',
      'content_translation_outdated',
      'content_translation_uid',
      'revision_log',
      'revision_uid',
      'preffered_admin_langcode',
      'preferred_langcode',
      'revision_created',
      'default_langcode',
      'revision_translation_affected',
    ];
  }
  public function getLoadEntities(): bool {
    return $this->loadEntities;
  }
  public function getLoadCustom(): bool {
    return $this->loadCustom;
  }
  public function getLoadProtected(): bool {
    return $this->loadProtected;
  }
}
