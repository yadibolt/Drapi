<?php

namespace Drupal\pingvin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\pingvin\Parser\RouteDocCommentParser;

class BlankController extends ControllerBase {
  public function viewTitleCallback() {
    return 'Blank Controller';
  }
}
