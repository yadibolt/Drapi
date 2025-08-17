<?php

namespace Drupal\pingvin\Sanitizer;

use Exception;

class InputSanitizer {
  protected const array ALLOWED_SANITIZERS = [
    'xss',
    'sql',
  ];

  /**
   * The input data to be sanitized.
   *
   * @var string|array
   */
  protected string|array $input;

  /**
   * Constructs a new InputSanitizer instance.
   *
   * @param string|array $input
   *    The input data to be sanitized.
   */
  public function __construct(string|array $input) {
    $this->input = $input;
  }

  /**
   * Sanitizes the input data using the specified sanitizer.
   *
   * @param string $sanitizer
   *    The sanitizer to use. @see self::ALLOWED_SANITIZERS for allowed values.
   *
   * @return string|array
   *    The sanitized input data.
   *
   * @throws Exception
   *    If the sanitizer is not allowed or not implemented.
   */
  public function sanitize(string $sanitizer): string|array {
    if (!in_array($sanitizer, self::ALLOWED_SANITIZERS)) {
      throw new Exception("Sanitizer value '{$sanitizer}' is not allowed.");
    }

    return match ($sanitizer) {
      'xss' => $this->sanitizeXSS($this->input),
      'sql' => $this->sanitizeSQL($this->input),
      default => throw new Exception("Sanitizer '{$sanitizer}' is not implemented."),
    };
  }

  /**
   * Sanitizes the input data to prevent XSS attacks.
   *
   * @param string|array $input
   *    The input data to be sanitized.
   *
   * @return string|array
   *    The sanitized input data.
   */
  protected function sanitizeXSS(string|array $input): string|array {
    $patterns = [
      '/<\s*script\b/i',
      '/on\w+\s*=/i',
      '/javascript:/i',
      '/<\s*iframe\b/i',
      '/<\s*img\b[^>]*on\w+\s*=/i',
    ];

    if (is_array($input)) {
      return array_map([$this, 'sanitizeXSS'], $input);
    }
    $sanitized = preg_replace($patterns, '', $input);
    return htmlspecialchars($sanitized, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }

  /**
   * Sanitizes the input data to prevent SQL injection.
   *
   * @param string|array $input
   *    The input data to be sanitized.
   *
   * @return string|array
   *    The sanitized input data.
   */
  protected function sanitizeSQL(string|array $input): string|array {
    $patterns = [
      '/\bUNION\b/i',
      '/\bSELECT\b/i',
      '/\bINSERT\b/i',
      '/\bUPDATE\b/i',
      '/\bDELETE\b/i',
      '/\bDROP\b/i',
      '/\bFROM\b/i',
      '/\bWHERE\b/i',
      '/\bOR\b\s+1=1/i',
      '/--/',
      '/#/',
      '/;/'
    ];

    if (is_array($input)) {
      return array_map([$this, 'sanitizeSQL'], $input);
    }

    $sanitized = preg_replace($patterns, '', $input);
    return str_replace(
      ["'", '"', '\\'],
      ['&#39;', '&quot;', '&#92;'],
      $sanitized
    );
  }
}
