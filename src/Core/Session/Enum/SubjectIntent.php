<?php

namespace Drupal\drift_eleven\Core\Session\Enum;

enum SubjectIntent: string {
  case ANONYMOUS = 'anonymous';
  case AUTHENTICATED = 'authenticated';
}
