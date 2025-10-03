<?php

namespace Drupal\drift_eleven\Core\Resolver;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\drift_eleven\Core\Field\BooleanField;
use Drupal\drift_eleven\Core\Field\ChangedField;
use Drupal\drift_eleven\Core\Field\CreatedField;
use Drupal\drift_eleven\Core\Field\DecimalField;
use Drupal\drift_eleven\Core\Field\EmailField;
use Drupal\drift_eleven\Core\Field\EntityReferenceField;
use Drupal\drift_eleven\Core\Field\FileField;
use Drupal\drift_eleven\Core\Field\FloatField;
use Drupal\drift_eleven\Core\Field\ImageField;
use Drupal\drift_eleven\Core\Field\IntegerField;
use Drupal\drift_eleven\Core\Field\LanguageField;
use Drupal\drift_eleven\Core\Field\LinkField;
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

class FieldResolver extends ResolverFoundation {
  /**
   * @var FieldItemListInterface[]
   */
  protected array $fields = [];

  /**
   * @param FieldItemListInterface[] $fields
   */
  public function setFields(array $fields): self {
    $this->fields = $fields;
    return $this;
  }

  public function resolveFields(bool $loadEntities): array {
    if (empty($this->fields)) {
      throw new InvalidArgumentException('No fields have been set for resolution.');
    }

    $resolvedFields = [];
    foreach ($this->fields as $fieldName => $field) {
      $fieldDef = $field->getFieldDefinition();
      $fieldType = $fieldDef->getType();

      $resolvedFields[] = match ($fieldType) {
        'integer' => new IntegerField()->getValue($field, $loadEntities),
        'decimal' => new DecimalField()->getValue($field, $loadEntities),
        'uuid' => new UUIDField()->getValue($field, $loadEntities),
        'language' => new LanguageField()->getValue($field, $loadEntities),
        'entity_reference' => new EntityReferenceField()->getValue($field, $loadEntities),
        'created' => new CreatedField()->getValue($field, $loadEntities),
        'changed' => new ChangedField()->getValue($field, $loadEntities),
        'timestamp' => new TimestampField()->getValue($field, $loadEntities),
        'email' => new EmailField()->getValue($field, $loadEntities),
        'file' => new FileField()->getValue($field, $loadEntities),
        'image' => new ImageField()->getValue($field, $loadEntities),
        'text' => new TextField()->getValue($field, $loadEntities),
        'text_long' => new TextLongField()->getValue($field, $loadEntities),
        'text_with_summary' => new TextWithSummary()->getValue($field, $loadEntities),
        'link' => new LinkField()->getValue($field, $loadEntities),
        'string_long' => new StringLongField()->getValue($field, $loadEntities),
        'string' => new StringField()->getValue($field, $loadEntities),
        'boolean' => new BooleanField()->getValue($field, $loadEntities),
        'path' => new PathField()->getValue($field, $loadEntities),
        'password' => null,
        'telephone' => new TelephoneField()->getValue($field, $loadEntities),
        'datetime' => null,
        'daterange' => null,
        'list_string' => null,
        'list_integer' => null,
        'list_float' => null,
        'float' => new FloatField()->getValue($field, $loadEntities),
      };
    }

    return $resolvedFields;
  }
}
