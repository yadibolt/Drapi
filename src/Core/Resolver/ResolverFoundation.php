<?php

namespace Drupal\drift_eleven\Core\Resolver;

class ResolverFoundation {
  protected array $supportedFieldTypes =[
    'string',
    'text',
    'text_long',
    'text_with_summary',
    'image',
    'link',
    'entity_reference',
    'boolean',
    'integer',
    'float',
    'decimal',
    'datetime',
    'timestamp',
    'email',
    'telephone',
    'uri',
    'file',
    'list_string',
    'list_integer',
    'list_float',
  ];

  public function getSupportedFieldTypes(): array {
    return $this->supportedFieldTypes;
  }
}