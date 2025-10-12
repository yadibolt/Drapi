<?php

namespace Drupal\drift_eleven\Core2\Session\Enum;

enum SubjectIntent: string {
  case ANONYMOUS = 'anonymous';
  case AUTHENTICATED = 'authenticated';
}
