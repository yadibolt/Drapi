<?php

namespace Drupal\drift_eleven\Core2\Cache;

use Drupal\drift_eleven\Core2\Cache\Interface\CacheInterface;
use Drupal\drift_eleven\Core2\Cache\Base\CacheBase;

class Cache extends CacheBase implements CacheInterface {
  public function __construct(string $binKey = '') {
    parent::__construct($binKey);
    // TODO implement configuration options here, providing default values for now.
  }

  public static function make(string $binKey = ''): Cache {
    return new self($binKey);
  }
}
