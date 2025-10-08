<?php

namespace Drupal\drift_eleven\Core2\Content\Field\Base;

use Drupal\Core\Field\FieldItemListInterface;

class FieldBase {
  protected bool $loadEntities;
  protected bool $loadCustom;
  protected bool $loadProtected;
  protected FieldItemListInterface $field;
  protected int|null|string $fieldName;
  protected mixed $values;

  public function __construct(FieldItemListInterface $field) {
    $this->field = $field;
    $this->loadEntities = false;
    $this->loadCustom = true;
    $this->loadProtected = false;
    $this->fieldName = $field->getName();
    $this->values = $field->getValue();
  }

  public function flattenValues(array $values): null|string|int|float|array {
    if (count($values) === 1) return $values[0];
    if (count($values) > 1) return array_map(fn($v) => $v, $values);

    return null;
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
  public function setField(FieldItemListInterface $field): self {
    $this->field = $field;
    return $this;
  }
  public function setFieldName(int|string|null $fieldName): self {
    $this->fieldName = $fieldName;
    return $this;
  }
  public function setValues(array $values): self {
    $this->values = $values;
    return $this;
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
  public function getField(): FieldItemListInterface {
    return $this->field;
  }
  public function getFieldName(): int|string|null {
    return $this->fieldName;
  }
  public function getValues(): mixed {
    return $this->values;
  }
}
