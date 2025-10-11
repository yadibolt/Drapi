<?php

namespace Drupal\drift_eleven\Core2\Session;

use Drupal\drift_eleven\Core2\Session\Base\SubjectBase;

class Subject extends SubjectBase {
  public static function make(): self {
    return new self();
  }
}
