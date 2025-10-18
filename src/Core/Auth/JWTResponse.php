<?php

namespace Drupal\drapi\Core\Auth;

use Drupal\drapi\Core\Auth\Enum\JWTResponseIntent;

class JWTResponse {
  protected string $action;
  protected bool $valid;
  protected bool $expired;
  protected bool $error;
  protected int $timestamp;

  public function __construct(JWTResponseIntent $intent) {
    switch ($intent) {
      case JWTResponseIntent::INVALID_FORMAT:
      case JWTResponseIntent::INVALID:
        $this->action = $intent->value;
        $this->valid = false;
        $this->expired = false;
        $this->error = true;
        break;
      case JWTResponseIntent::EXPIRED:
        $this->action = $intent->value;
        $this->valid = false;
        $this->expired = true;
        $this->error = false;
        break;
      case JWTResponseIntent::OK:
        $this->action = $intent->value;
        $this->valid = true;
        $this->expired = false;
        $this->error = false;
        break;
    }

    $this->timestamp = time();
  }

  public function getAction(): string {
    return $this->action;
  }
  public function isValid(): bool {
    return $this->valid;
  }
  public function isExpired(): bool {
    return $this->expired;
  }
  public function hasError(): bool {
    return $this->error;
  }
  public function getTimestamp(): int {
    return $this->timestamp;
  }
}
