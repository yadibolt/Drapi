<?php

namespace Drupal\drift_eleven\Core\Parser;

interface FileParserInterface {force-commit
  /**
   * Parses the content of a file and returns an associative array of the parsed data.
   *
   * @param string $content the content to parse.
   * @param bool $fillMissing whether to fill missing values with null or defaults.
   * @return array|null the parsed data as an associative array, or null if parsing fails.
   */
  public static function parse(string $content, bool $fillMissing = false): ?array;
}
