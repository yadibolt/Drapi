<?php

namespace Drupal\drift_eleven\Core\Session;

use Drupal\drift_eleven\Core\Session\Base\SessionBase;

class Session extends SessionBase {
  public static function make(string $token = ''): self {
    return new self($token);
  }
}
