<?php

namespace Drupal\drift_eleven\Core\Resolver;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drift_eleven\Core\Field\BooleanField;
use Drupal\drift_eleven\Core\Field\ChangedField;
use Drupal\drift_eleven\Core\Field\CreatedField;
use Drupal\drift_eleven\Core\Field\DateRangeField;
use Drupal\drift_eleven\Core\Field\DatetimeField;
use Drupal\drift_eleven\Core\Field\DecimalField;
use Drupal\drift_eleven\Core\Field\EmailField;
use Drupal\drift_eleven\Core\Field\EntityReferenceField;
use Drupal\drift_eleven\Core\Field\FieldFoundation;
use Drupal\drift_eleven\Core\Field\FileField;
use Drupal\drift_eleven\Core\Field\FloatField;
use Drupal\drift_eleven\Core\Field\ImageField;
use Drupal\drift_eleven\Core\Field\IntegerField;
use Drupal\drift_eleven\Core\Field\LanguageField;
use Drupal\drift_eleven\Core\Field\LinkField;
use Drupal\drift_eleven\Core\Field\ListFloatField;
use Drupal\drift_eleven\Core\Field\ListIntergerField;
use Drupal\drift_eleven\Core\Field\ListStringField;
use Drupal\drift_eleven\Core\Field\PathField;
use Drupal\drift_eleven\Core\Field\StringField;
use Drupal\drift_eleven\Core\Field\StringLongField;
use Drupal\drift_eleven\Core\Field\TelephoneField;
use Drupal\drift_eleven\Core\Field\TextField;
use Drupal\drift_eleven\Core\Field\TextLongField;
use Drupal\drift_eleven\Core\Field\TextWithSummary;
use Drupal\drift_eleven\Core\Field\TimestampField;
use Drupal\drift_eleven\Core\Field\UUIDField;
use InvalidArgumentException;

class FieldResolver extends FieldFoundation {
  /**
   * @var FieldItemListInterface[]
   */
  protected array $fields = [];

  public bool $customFieldsOnly = true;
  public bool $loadEntities = false;

  public bool $includeProtectedFields = false;

  /**
   * @param FieldItemListInterface[] $fields
   */
  public function setFields(array $fields, array $options = []): self {
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

    $this->fields = $fields;
    return $this;
  }

  public function resolveFields(): array {
    if (empty($this->fields)) {
      throw new InvalidArgumentException('No fields have been set for resolution.');
    }

    $resolvedFields = [];
    foreach ($this->fields as $fieldName => $field) {
      $fieldDef = $field->getFieldDefinition();
      $fieldType = $fieldDef->getType();

      if (in_array($fieldName, $this->getBaseFieldNames()) && $this->customFieldsOnly) continue;
      if (in_array($fieldName, $this->getProtectedFieldNames()) && !$this->includeProtectedFields) continue;

      $resolvedFields[$fieldName] = match ($fieldType) {
        'integer' => new IntegerField()->getValue($field, $this->loadEntities),
        'decimal' => new DecimalField()->getValue($field, $this->loadEntities),
        'uuid' => new UUIDField()->getValue($field, $this->loadEntities),
        'language' => new LanguageField()->getValue($field, $this->loadEntities),
        'entity_reference' => new EntityReferenceField()->getValue($field, [
          'customFieldsOnly' => $this->customFieldsOnly,
          'includeProtectedFields' => $this->includeProtectedFields,
          'loadEntities' => $this->loadEntities,
        ]),
        'created' => new CreatedField()->getValue($field, $this->loadEntities),
        'changed' => new ChangedField()->getValue($field, $this->loadEntities),
        'timestamp' => new TimestampField()->getValue($field, $this->loadEntities),
        'email', 'mail' => new EmailField()->getValue($field, $this->loadEntities),
        'file' => new FileField()->getValue($field, $this->loadEntities),
        'image' => new ImageField()->getValue($field, $this->loadEntities),
        'text' => new TextField()->getValue($field, $this->loadEntities),
        'text_long' => new TextLongField()->getValue($field, $this->loadEntities),
        'text_with_summary' => new TextWithSummary()->getValue($field, $this->loadEntities),
        'link' => new LinkField()->getValue($field, $this->loadEntities),
        'string_long' => new StringLongField()->getValue($field, $this->loadEntities),
        'string' => new StringField()->getValue($field, $this->loadEntities),
        'boolean' => new BooleanField()->getValue($field, $this->loadEntities),
        'path' => new PathField()->getValue($field, $this->loadEntities),
        'password' => null,
        'telephone' => new TelephoneField()->getValue($field, $this->loadEntities),
        'datetime' => new DatetimeField()->getValue($field, $this->loadEntities),
        'daterange' => new DateRangeField()->getValue($field, $this->loadEntities),
        'list_string' => new ListStringField()->getValue($field, $this->loadEntities),
        'list_integer' => new ListIntergerField()->getValue($field, $this->loadEntities),
        'list_float' => new ListFloatField()->getValue($field, $this->loadEntities),
        'float' => new FloatField()->getValue($field, $this->loadEntities),
      };
    }

    return $resolvedFields;
  }

  // recursively remove values with names as a key like in getProtectedFieldNames()
  public function removeFields(array $fields, string $type): array {
    if (empty($fields)) return [];

    $fieldsToRemove = match ($type) {
      'base' => $this->getBaseFieldNames(),
      'protected' => $this->getProtectedFieldNames(),
      default => [],
    };

    if (empty($fieldsToRemove)) {
      throw new InvalidArgumentException('The type must be either "base" or "protected".');
    }

    foreach ($fields as $key => $value) {
      if (in_array($key, $fieldsToRemove)) {
        unset($fields[$key]);
      } elseif (is_array($value)) {
        $fields[$key] = $this->removeFields($value, $type);
      }
    }

    return $fields;
  }
}
