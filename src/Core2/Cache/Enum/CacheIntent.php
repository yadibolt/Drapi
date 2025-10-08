<?php

namespace Drupal\drift_eleven\Core2\Cache\Enum;

enum CacheIntent: string {
  case ENTITY = 'entity';
  case URL = 'url';
}
