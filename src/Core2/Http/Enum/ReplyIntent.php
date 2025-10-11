<?php

namespace Drupal\drift_eleven\Core2\Http\Enum;

enum ReplyIntent: string {
  case INVALID_HEADER = 'invalid_header';
  case INVALID_PAYLOAD = 'invalid_payload';
  case INVALID_TOKEN = 'invalid_token';
  case UNAUTHORIZED = 'unauthorized';
  case SESSION_FORBIDDEN = 'session_forbidden';
  case SESSION_EXPIRED = 'session_expired';
  case SESSION_INVALID = 'session_invalid';
  case SESSION_NOT_FOUND = 'session_not_found';
  case USER_INACTIVE = 'user_inactive';
  case USER_BLOCKED = 'user_blocked';
  case USER_NOT_FOUND = 'user_not_found';
  case USER_FORBIDDEN = 'user_forbidden';
  case REQUIREMENTS_NOT_MET = 'requirements_not_met';
  case INVALID_CONTENT_TYPE = 'invalid_content_type';
}
