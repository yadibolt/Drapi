<?php

namespace Drupal\drift_eleven\Core2\Content\Field\Interface;

interface FieldInterface {
  public function getFieldValues($options = []): null|string|int|float|array;
}
