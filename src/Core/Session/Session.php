<?php

namespace Drupal\drapi\Core\Session;

use Drupal\drapi\Core\Session\Base\SessionBase;

class Session extends SessionBase {
  public static function make(string $token = '', string $userAgent = '', string $ip = ''): self {
    return new self($token, $userAgent, $ip);
  }
}
