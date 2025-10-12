<?php

namespace Drupal\drift_eleven\Core2\Auth\Enum;

enum JWTIntent: string {
  case ACCESS_TOKEN = 'access_token';
  case REFRESH_TOKEN = 'refresh_token';
  case RESET_PASSWORD = 'reset_password';
}
