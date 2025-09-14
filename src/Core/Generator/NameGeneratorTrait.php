<?php

namespace Drupal\drift_eleven\Core\Generator;

use InvalidArgumentException;
use Random\RandomException;

trait NameGeneratorTrait {
  /**
   * @throws RandomException
   */
  public static function genFileName(string $basename): string {
    if (!str_contains($basename, '.')) throw new InvalidArgumentException("Filename must contain an extension.");

    $fileSplit = explode('.', $basename);
    $extension = $fileSplit[count($fileSplit) - 1];
    $name = substr(implode('.', array_slice($fileSplit, 0, -1)), 0, 24);

    $rand = random_int(0, 999);

    return $name . '_' . bin2hex((time() - $rand)) . '.' . $extension;
  }
}
