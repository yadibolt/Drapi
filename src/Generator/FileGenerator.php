<?php

namespace Drupal\pingvin\Generator;

use Drupal\pingvin\Normalizer\FileNormalizer;
use Random\RandomException;

class FileGenerator {
  /**
   * @throws RandomException
   */
  public static function generateFilename(string $filename, string $fileExtension): string {
    $filename = str_replace('.' . $fileExtension, '', $filename);
    $filename = FileNormalizer::normalizeFilename($filename);

    $random = random_int(0, 999);

    return substr($filename, 0, 24) . '_' . bin2hex((time() - $random));
  }
}
