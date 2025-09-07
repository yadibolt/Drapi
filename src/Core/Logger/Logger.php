<?php

namespace Drupal\drift_eleven\Core\Logger;

use Drupal;
use InvalidArgumentException;

class Logger implements LoggerInterface {
  public static function l(string $message, array $context = [], string $logLevel = self::LOG_LEVEL_DEFAULT): void {
    if (!in_array($logLevel, self::LOG_LEVELS)) {
      throw new InvalidArgumentException('You have specified invalid log level. Available log levels are: ' . implode(', ', self::LOG_LEVELS));
    }

    $logger = Drupal::logger(self::LOG_CHANNEL);
    match($logLevel) {
      'debug' => $logger->debug($message, $context),
      'error' => $logger->error($message, $context),
      'notice' => $logger->notice($message, $context),
      'info' => $logger->info($message, $context),
      'critical' => $logger->critical($message, $context),
      'emergency' => $logger->emergency($message, $context),
      'alert' => $logger->alert($message, $context),
      default => $logger->warning($message, $context),
    };
  }
}
