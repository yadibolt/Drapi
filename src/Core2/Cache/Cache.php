<?php

namespace Drupal\drift_eleven\Core2\Cache;

use Drupal\drift_eleven\Core2\Cache\Interface\CacheInterface;
use Drupal\drift_eleven\Core2\Cache\Base\CacheBase;

class Cache extends CacheBase implements CacheInterface {
  public function __construct() {
    parent::__construct();
    // TODO implement configuration options here, providing default values for now.
    $def_cacheBinKey = 'de_bin';
    $def_cacheDuration = 3600;

    $this->setCacheBinKey($def_cacheBinKey);
    $this->setCacheDuration($def_cacheDuration);
  }

  public static function make(): Cache {
    return new self();
  }
}
