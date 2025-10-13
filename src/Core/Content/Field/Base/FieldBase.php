<?php

namespace Drupal\drift_eleven\Core\Content\Field\Base;

use Drupal\Core\Field\FieldItemListInterface;

abstract class FieldBase {
  protected bool $loadEntities;
  protected bool $loadCustom;
  protected bool $loadProtected;
  protected bool $stripFieldPrefixes;
  protected FieldItemListInterface $field;
  protected int|null|string $fieldName;
  protected mixed $values;

  public function __construct(FieldItemListInterface $field) {
    $this->field = $field;
    $this->loadEntities = false;
    $this->loadCustom = true;
    $this->loadProtected = false;
    $this->stripFieldPrefixes = false;
    $this->fieldName = $field->getName();
    $this->values = $field->getValue();
  }

  public function flattenValues(?array $values): null|bool|string|int|float|array {
    if (empty($values)) return null;

    if (count($values) === 1) {
      if (is_object($values[0])) {
        if (method_exists($values[0], 'toArray')) {
          return $values[0]->toArray();
        }
      } else {
        return $values[0];
      }
    }
    if (count($values) > 1) {
      $arrayValues = [];
      foreach ($values as $value) {
        if (is_object($value)) {
          if (method_exists($value, 'toArray')) {
            $arrayValues[] = $value->toArray();
          }
        } else {
          $arrayValues[] = $value;
        }
      }
      return $arrayValues;
    }

    return null;
  }

  protected function handleOptions(array $options): self {
    if (isset($options['load_entities']) && is_bool($options['load_entities'])) $this->setLoadEntities($options['load_entities']);
    if (isset($options['load_custom']) && is_bool($options['load_custom'])) $this->setLoadCustom($options['load_custom']);
    if (isset($options['load_protected']) && is_bool($options['load_protected'])) $this->setLoadProtected($options['load_protected']);
    if (isset($options['strip_field_prefixes']) && is_bool($options['strip_field_prefixes'])) $this->setStripFieldPrefixes($options['strip_field_prefixes']);

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
  public function setStripFieldPrefixes(bool $stripFieldPrefixes): self {
    $this->stripFieldPrefixes = $stripFieldPrefixes;
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
  public function getStripFieldPrefixes(): bool {
    return $this->stripFieldPrefixes;
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
