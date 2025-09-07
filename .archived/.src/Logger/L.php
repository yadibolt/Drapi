<?php

namespace Drupal\pingvin\Logger;

use Drupal;

class L {
  /**
   * Available Drupal log levels.
   *
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
   * We use wrapper around Drupal logger to log messages.
   *
   * @param string $message
   *   The log message with placeholders for tokens.
   * @param array $tokens
   *  An associative array of tokens to replace in the message.
   * @param string $log_level
   *  The log level. Default is 'error'.
   * @return void
   */
  public static function log(string $message, array $tokens, string $log_level = 'error'): void {
    if (!in_array($log_level, self::LOG_LEVELS)) {
      $log_level = 'error';
    }

    $caller = Drupal::logger(pw8dr1_PROJECT_ID . '_logs');
    match($log_level) {
      'debug' => $caller->debug($message, $tokens),
      'warning' => $caller->warning($message, $tokens),
      'notice' => $caller->notice($message, $tokens),
      'info' => $caller->info($message, $tokens),
      'critical' => $caller->critical($message, $tokens),
      'emergency' => $caller->emergency($message, $tokens),
      'alert' => $caller->alert($message, $tokens),
      default => $caller->error($message, $tokens),
    };
  }
}
