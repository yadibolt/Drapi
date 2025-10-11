<?php

namespace Drupal\drift_eleven\Core2\Session\Base;

use Drupal\drift_eleven\Core2\Session\Subject;

class SessionBase {
  protected string $token = '';
  protected Subject $subject;

  public function __construct(string $token) {
    $this->token = $token;
  }
}
