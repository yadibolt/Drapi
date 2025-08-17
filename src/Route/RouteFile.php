<?php

namespace Drupal\pingvin\Route;

use Drupal\pingvin\Asserter\RouteClassDeclarationAsserter;
use Drupal\pingvin\Asserter\RouteClassInterfaceAsserter;
use Drupal\pingvin\Asserter\RouteClassMethodAsserter;
use Drupal\pingvin\Asserter\RouteClassDocCommentAsserter;
use Exception;

class RouteFile {
  /**
   * The path of Route file to be processed.
   * @var string
   */
  private string $filePath;

  /**
   * Constructs a new Route File instance.
   *
   * @param string $filePath
   *    The path to the Route file.
   * @throws Exception
   *    If the file does not exist, is not readable, is not a file, or is not a PHP file.
   */
  public function __construct(string $filePath) {
    if (!file_exists($filePath)) {
      throw new Exception("The file at {$filePath} does not exist.");
    }

    if (!is_readable($filePath)) {
      throw new Exception("The file at {$filePath} is not readable.");
    }

    if (!is_file($filePath)) {
      throw new Exception("The path {$filePath} is not a valid file.");
    }

    if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
      throw new Exception("The file at {$filePath} is not a PHP file.");
    }

    $this->filePath = $filePath;
  }

  public function isValid(): bool {
    $asserters = [
      RouteClassDeclarationAsserter::class,
      RouteClassMethodAsserter::class,
      RouteClassDocCommentAsserter::class,
      RouteClassInterfaceAsserter::class,
    ];

    foreach ($asserters as $asserter) {
      try {
        call_user_func([$asserter, 'assert'], $this);
      } catch (Exception $e) {
        throw new Exception("{$asserter} requirements not met.\n{$e->getMessage()}");
      }
    }

    return true;
  }

  public function getFilePath(): string {
    return $this->filePath;
  }
}
