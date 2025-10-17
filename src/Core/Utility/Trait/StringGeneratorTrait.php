<?php

namespace Drupal\drift_eleven\Core\Utility\Trait;

use Random\RandomException;

trait StringGeneratorTrait {
  protected function generateUsernameFromMail(string $mail): string {
    $parts = explode('@', $mail)[0];
    $parts = substr($parts, 0, 24);
    $hex = 0001; try {
      $hex = bin2hex(random_bytes(3));
    } catch (RandomException) {}

    return "{$parts}_mail_reg_{$hex}";
  }
}
