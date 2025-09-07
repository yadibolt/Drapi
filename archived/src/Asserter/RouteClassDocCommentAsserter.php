<?php

namespace Drupal\pingvin\Asserter;

use Drupal\pingvin\File\Retriever;
use Drupal\pingvin\Parser\RouteDocCommentParser;
use Drupal\pingvin\Route\RouteFile;
use Drupal\pingvin\Route\Route;
use Exception;

class RouteClassDocCommentAsserter {
  /**
   * Validates the doc comment declaration in a route file.
   *
   * @param RouteFile $file
   *     The path to the PHP file to validate.
   * @return void
   * @throws Exception
   *    If the file does not match the expected criteria.
   */
  public static function assert(RouteFile $file): void {
    $filePath = $file->getFilePath();
    if (!file_exists($filePath)) {
      throw new Exception("File does not exist: {$filePath}");
    }

    $attributeRetriever = new Retriever($filePath);
    $docComment = $attributeRetriever->retrieve('docComment');

    if (empty($docComment)) {
      throw new Exception('Class does not have a doc comment.');
    }

    $parser = new RouteDocCommentParser($docComment);

    try {
      $json = $parser->parse();
    } catch (Exception $e) {
      throw new Exception('Error parsing class doc comment: ' . $e->getMessage());
    }

    // assert types
    // attributes that have leading * are required

    // *id
    if (!isset($json['id']) || !is_string($json['id'])) {
      throw new Exception('The route id must be a string.');
    }

    // *name
    if (!isset($json['name']) || !is_string($json['name'])) {
      throw new Exception('The route name must be a string.');
    }

    // *method
    if (!isset($json['method']) || !is_string($json['method'])) {
      throw new Exception('The route method must be a string.');
    }

    if (!in_array(strtoupper($json['method']), Route::ALLOWED_ROUTE_METHODS)) {
      throw new Exception('The route method must be one of: ' . implode(', ', Route::ALLOWED_ROUTE_METHODS) . '.');
    }

    // all http methods are lowercased for the class method to be validated
    $retriever = new Retriever($filePath);
    $publicMethods = $retriever->retrieve('publicMethods');
    if (!in_array(strtolower($json['method']), $publicMethods)) {
      throw new Exception('The class must define a public method that matches the http method.');
    }

    // description
    if (isset($json['description']) && !is_string($json['description'])) {
      throw new Exception('The route description must be a string.');
    }

    // *path
    if (!isset($json['path']) || !is_string($json['path'])) {
      throw new Exception('The route path must be a string.');
    }

    // permissions
    if (isset($json['permissions']) && !is_array($json['permissions'])) {
      throw new Exception('The route permissions must be an array.');
    }

    if (isset($json['permissions'])) {
      foreach ($json['permissions'] as $host) {
        if (!is_string($host)) {
          throw new Exception('Each permission must be a string.');
        }
      }
    }

    // roles
    if (isset($json['roles']) && !is_array($json['roles'])) {
      throw new Exception('The route roles must be an array.');
    }

    if (isset($json['roles'])) {
      foreach ($json['roles'] as $host) {
        if (!is_string($host)) {
          throw new Exception('Each role must be a string.');
        }
      }
    }

    // restrict host
    if (isset($json['restrict_host']) && !is_string($json['restrict_host'])) {
      throw new Exception('The route restrict_host must be a string.');
    }

    // cacheable
    if (isset($json['cacheable']) && !is_bool($json['cacheable'])) {
      throw new Exception('The route cache must be a boolean.');
    }
  }
}
