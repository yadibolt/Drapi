<?php

namespace Drupal\drift_eleven\Core\Session;

use Drupal\drift_eleven\Core\Session\Base\SessionBase;

class Session extends SessionBase {
  public static function make(string $token = '', string $userAgent = '', string $ip = ''): self {
    return new self($token, $userAgent, $ip);
  }
}
