<?php

namespace Drupal\pingvin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\pingvin\Parser\RouteDocCommentParser;

class BlankController extends ControllerBase {
  public function viewTitleCallback() {


    $rdcp = new RouteDocCommentParser($content);
    $parsed = $rdcp->parse();

    var_dump($parsed);

    return 'Blank Controller';
  }
}
