<?php

namespace Drupal\drapi\Core\Session\Enum;

enum SubjectIntent: string {
  case ANONYMOUS = 'anonymous';
  case AUTHENTICATED = 'authenticated';
}
