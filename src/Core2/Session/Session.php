<?php

namespace Drupal\drift_eleven\Core2\Session;

use Drupal\drift_eleven\Core2\Session\Base\SessionBase;

class Session extends SessionBase {
  public static function make(string $token = ''): self {
    return new self($token);
  }
}
