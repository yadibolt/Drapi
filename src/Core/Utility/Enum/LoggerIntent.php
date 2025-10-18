<?php

namespace Drupal\drapi\Core\Utility\Enum;

enum LoggerIntent: string {
  case DEBUG = 'debug';
  case INFO = 'info';
  case NOTICE = 'notice';
  case WARNING = 'warning';
  case ERROR = 'error';
  case CRITICAL = 'critical';
  case ALERT = 'alert';
  case EMERGENCY = 'emergency';
}
