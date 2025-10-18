<?php

namespace Drupal\drapi\Core\Cache;

use Drupal\drapi\Core\Cache\Interface\CacheInterface;
use Drupal\drapi\Core\Cache\Base\CacheBase;

class Cache extends CacheBase implements CacheInterface {
  public function __construct(string $binKey = '') {
    parent::__construct($binKey);
    // TODO implement configuration options here, providing default values for now.
  }

  public static function make(string $binKey = ''): self {
    return new self($binKey);
  }
}
