<?php

namespace Drupal\drapi\Core\Utility;

class Base64 {
  public static function encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }
  public static function decode(string $data): string {
    $data = strtr($data, '-_', '+/');
    return base64_decode(str_pad($data, strlen($data) % 4, '=', STR_PAD_RIGHT));
  }
}
