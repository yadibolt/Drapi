<?php

namespace Drupal\pingvin\Asserter;

use Drupal\pingvin\File\Retriever;
use Drupal\pingvin\Route\RouteFile;
use Drupal\pingvin\Route\Route;
use Exception;

class RouteClassMethodAsserter {
  /**
   * This method checks if the class has public methods, ensures that it does not
   * have any methods starting with double underscores, and verifies that it
   * defines exactly one of the allowed route methods.
   *
   * @param RouteFile $file
   *      The path to the PHP file to validate.
   * @return void
   * @throws Exception
   *    If the file does not match the expected criteria.
   */
  public static function assert(RouteFile $file): void {
    $filePath = $file->getFilePath();
    if (!file_exists($filePath)) {
      throw new Exception("File does not exist: $filePath");
    }

    $attributeRetriever = new Retriever($filePath);
    $methods = $attributeRetriever->retrieve('publicMethods');

    if (empty($methods)) {
      throw new Exception('Class does not have any public methods.');
    }

    $numMethodsDefined = 0;
    // route file must not have constructor or other __methods
    foreach ($methods as $method) {
      if (str_starts_with($method, '__')) {
        throw new Exception('Class has a method that starts with double underscore: ' . $method);
      }
      if ($method !== strtolower($method)) {
        throw new Exception('Class has a method that is not all lowercase: ' . $method);
      }
      if (in_array(strtoupper($method), Route::ALLOWED_ROUTE_METHODS)) {
        $numMethodsDefined += 1;
      }
    }

    if ($numMethodsDefined === 0) {
      throw new Exception('Class does not define any of the supported methods: ' . implode(', ', Route::ALLOWED_ROUTE_METHODS));
    }

    if ($numMethodsDefined > 1) {
      throw new Exception('Class defines more than one supported method: ' . implode(', ', Route::ALLOWED_ROUTE_METHODS));
    }
  }
}
