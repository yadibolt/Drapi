<?php

namespace Drupal\drift_eleven\Core2\Http;

use Drupal\drift_eleven\Core2\Http\Base\ReplyBase;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Reply extends ReplyBase {
  public function __construct(array|string $data, int $status = 200, ResponseHeaderBag|array $headers = []) {
    parent::__construct($data, $status, $headers);
  }

  public static function make(array|string $data, int $status = 200, ResponseHeaderBag|array $headers = []): Reply {
    return new self($data, $status, $headers);
  }
}
