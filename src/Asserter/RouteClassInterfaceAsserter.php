<?php

namespace Drupal\pingvin\Asserter;

use Drupal\pingvin\File\Retriever;
use Drupal\pingvin\Route\RouteFile;
use Exception;

class RouteClassInterfaceAsserter {
  /**
   * Asserts that the given file implements the required RouteInterface.
   *
   * @param RouteFile $file
   *     The path to the PHP file to validate.
   * @return void
   * @throws Exception
   *    If the file does not implement the required interface or if the file is invalid.
   */
  public static function assert(RouteFile $file): void {
    $filePath = $file->getFilePath();
    if (!file_exists($filePath)) {
      throw new Exception("File does not exist: {$filePath}");
    }

    $attributeRetriever = new Retriever($filePath);
    $interfaces = $attributeRetriever->retrieve('interfaces');

    if (empty($interfaces)) {
      throw new Exception("The file at {$filePath} does not implement any interfaces.");
    }

    if (!is_array($interfaces)) {
      throw new Exception("The 'interfaces' attribute retrieved from {$filePath} is not an array.");
    }

    $interfaceNamespace = 'Drupal\\' . PROJECT_ID . '\Route\RouteInterface';
    if (!in_array($interfaceNamespace, $interfaces)) {
      throw new Exception("The file at {$filePath} does not implement the required interface $interfaceNamespace.");
    }
  }
}
