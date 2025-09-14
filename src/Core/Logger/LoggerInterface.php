<?php

namespace Drupal\drift_eleven\Core\Logger;

interface LoggerInterface {
  /**
   * Log level name used for \Drupal::logger()
   * @var string
   */
  public const string LOG_CHANNEL = D9M7_LOGGER_KEY;
  public const string LEVEL_DEBUG = 'debug';
  public const string LEVEL_INFO = 'info';
  public const string LEVEL_WARNING = 'warning';
  public const string LEVEL_ERROR = 'error';
  public const string LEVEL_CRITICAL = 'critical';
  public const string LEVEL_ALERT = 'alert';
  public const string LEVEL_EMERGENCY = 'emergency';
  public const string LEVEL_NOTICE = 'notice';
  /**
   * All Drupal log levels used by this logger
   * @var array
   */
  public const array LOG_LEVELS = [
    self::LEVEL_DEBUG,
    self::LEVEL_WARNING,
    self::LEVEL_ERROR,
    self::LEVEL_NOTICE,
    self::LEVEL_INFO,
    self::LEVEL_CRITICAL,
    self::LEVEL_EMERGENCY,
    self::LEVEL_ALERT,
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
