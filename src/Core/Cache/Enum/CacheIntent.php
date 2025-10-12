<?php

namespace Drupal\drift_eleven\Core\Cache\Enum;

enum CacheIntent: string {
  case ENTITY = 'entity';
  case URL = 'url';
}
