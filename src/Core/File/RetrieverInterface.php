<?php

namespace Drupal\drift_eleven\Core\File;

interface RetrieverInterface {
  /**
   * Retrieves an attribute values from a file
   * @param string $filePath path to the file
   * @param string $attribute attribute to retrieve
   * @return string|array|null
   */
  public static function retrieve(string $filePath, string $attribute): string|array|null;
}
