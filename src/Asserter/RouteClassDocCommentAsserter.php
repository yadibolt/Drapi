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

    $retriever = new Retriever($filePath);
    $publicMethods = $retriever->retrieve('publicMethods');
    if (!in_array(strtoupper($json['method']), strtoupper($publicMethods))) {
      throw new Exception('The route method must be defined one of the public methods of the class.');
    }

    // description
    if (isset($json['description']) && !is_string($json['description'])) {
      throw new Exception('The route description must be a string.');
    }

    // *path
    if (!isset($json['path']) || !is_string($json['path'])) {
      throw new Exception('The route path must be a string.');
    }

    // permission
    if (isset($json['permission']) && !is_array($json['permission'])) {
      throw new Exception('The route permission must be an array.');
    }

    if (isset($json['permission'])) {
      foreach ($json['permission'] as $host) {
        if (!is_string($host)) {
          throw new Exception('Each host in permission must be a string.');
        }
      }
    }

    // restrict host
    if (isset($json['restrict_host']) && !is_array($json['restrict_host'])) {
      throw new Exception('The route restrict_host must be an array.');
    }

    if (isset($json['restrict_host'])) {
      foreach ($json['restrict_host'] as $host) {
        if (!is_string($host)) {
          throw new Exception('Each host in restrict_host must be a string.');
        }
      }
    }

    // enable cache
    if (isset($json['enable_cache']) && !is_bool($json['enable_cache'])) {
      throw new Exception('The route enable_cache must be a boolean.');
    }
  }
}
