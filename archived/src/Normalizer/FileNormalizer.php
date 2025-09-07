<?php

namespace Drupal\pingvin\Normalizer;

use Transliterator;

class FileNormalizer {
  public static function normalizeFilename(string $filename): string {
    // @link https://www.php.net/manual/en/book.intl.php
    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
    return $transliterator->transliterate($filename);
  }

  public static function normalizeFileExtension(string $fileExtension): string {
    return strtolower($fileExtension);
  }
}
