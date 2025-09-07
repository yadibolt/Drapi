<?php

namespace Drupal\pingvin\Asserter;

use Drupal\pingvin\File\Retriever;
use Drupal\pingvin\Route\RouteFile;
use Exception;

class RouteClassDeclarationAsserter {
  /**
   * Validates the class declaration in a PHP file.
   *
   * @param RouteFile $file
   *    The path to the PHP file to validate.
   * @return void
   * @throws Exception
   *   If the file does not match the expected criteria.
   */
  public static function assert(RouteFile $file): void {
    $filePath = $file->getFilePath();
    if (!file_exists($filePath)) {
      throw new Exception("File does not exist: {$filePath}");
    }

    $attributeRetriever = new Retriever($filePath);
    $fileName = pathinfo($filePath, PATHINFO_FILENAME);
    $className = $attributeRetriever->retrieve('shortName');

    if ($className !== $fileName) {
      throw new Exception('File name does not match class name: ' . $fileName . ' vs ' . $className);
    }

    if (!ctype_upper(str_split($fileName)[0])) {
      throw new Exception('File name must start with an uppercase letter: ' . $fileName);
    }
  }
}
