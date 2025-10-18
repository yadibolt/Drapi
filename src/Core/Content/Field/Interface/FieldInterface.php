<?php

namespace Drupal\drapi\Core\Content\Field\Interface;

interface FieldInterface {
  public function getFieldValues(array $options = []): null|bool|string|int|float|array;
}
