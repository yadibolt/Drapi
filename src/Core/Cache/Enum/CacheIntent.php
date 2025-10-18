<?php

namespace Drupal\drapi\Core\Cache\Enum;

enum CacheIntent: string {
  case ENTITY = 'entity';
  case URL = 'url';
}
