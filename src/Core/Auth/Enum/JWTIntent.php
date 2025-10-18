<?php

namespace Drupal\drapi\Core\Auth\Enum;

enum JWTIntent: string {
  case ACCESS_TOKEN = 'access_token';
  case ACCESS_TOKEN_UNLIMITED = 'access_token_unlimited';
  case REFRESH_TOKEN = 'refresh_token';
  case RESET_PASSWORD = 'reset_password';
}
