<?php

namespace Drupal\drift_eleven\Core\Field;

interface FieldInterface
{
  public const bool CUSTOM_FIELDS_ONLY = true;
  public const bool LOAD_ENTITIES = false;
  public const bool INCLUDE_PROTECTED_FIELDS = false;
}
