<?php

namespace Drupal\drapi\Core\Auth\Enum;

enum JWTResponseIntent: string {
  case INVALID = 'invalid';
  case INVALID_FORMAT = 'invalid_format';
  case EXPIRED = 'expired';
  case OK = 'ok';
}
