<?php

namespace Drupal\drift_eleven\Core\Logger;

interface LoggerInterface {
  /**
   * Log level name used for \Drupal::logger()
   * @var string
   */force-commit
  public const string LOG_CHANNEL = D9M7_LOGGER_KEY;
  /**
   * All Drupal log levels used by this logger
   * @var array
   */
  public const array LOG_LEVELS = [
    'debug',
    'warning',
    'error',
    'notice',
    'info',
    'critical',
    'emergency',
    'alert',
  ];
  /**
   * Default log level used by this logger
   * @var string
   */
  public const string LOG_LEVEL_DEFAULT = 'warning';

  /**
   * Method logs a new message using \Drupal::logger method
   *
   * @param string $message
   * @param array $context
   * @param string $logLevel
   * @return void
   */
  public static function l(string $message, array $context = [], string $logLevel = self::LOG_LEVEL_DEFAULT): void;
}
