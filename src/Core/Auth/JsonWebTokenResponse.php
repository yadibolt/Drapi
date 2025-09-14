<?php

namespace Drupal\drift_eleven\Core\Auth;

use InvalidArgumentException;

class JsonWebTokenResponse implements JsonWebTokenResponseInterface {
  public string $action;
  public bool $valid;
  public bool $expired;
  public bool $error;
  public int $timestamp;

  public function __construct(string $action, bool $valid, bool $expired, bool $error = false) {
    if (!in_array($action, self::ACTION_TYPES)) throw new InvalidArgumentException("Action '$action' is not valid.");

    $this->action = $action;
    $this->valid = $valid;
    $this->expired = $expired;
    $this->error = $error;
    $this->timestamp = time();
  }
}
