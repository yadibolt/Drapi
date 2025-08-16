<?php

namespace Drupal\pingvin\Util;

class Base64 {
  /**
   * Encodes a string into a URL-safe base64 encoded string.
   *
   * @param string $payload
   *    The string that should be encoded.
   * @return string
   *    Returns a string in a form of a URL-safe base64 encoded string.
   */
  public static function encode(string $payload): string {
    return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
  }

  /**
   * Decodes a URL-safe base64 encoded string.
   *
   * @param string $payload
   *   The URL-safe base64 encoded string that should be decoded.
   * @return string
   *    Returns a decoded string from a URL-safe base64 encoded string.
   */
  public static function decode(string $payload): string {
    $payload = strtr($payload, '-_', '+/');

    return base64_decode(str_pad($base64, strlen($base64) % 4, '=', STR_PAD_RIGHT));
  }
}
