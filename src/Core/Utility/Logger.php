<?php

namespace Drupal\drift_eleven\Core\Utility;

use Drupal;
use Drupal\drift_eleven\Core\Utility\Enum\LoggerIntent;

class Logger {
  protected const string LOGGER_CHANNEL = LOGGER_CHANNEL_DEFAULT;
  protected const string LOGGER_LEVEL = LOGGER_LEVEL_DEFAULT;

  protected string $channel = self::LOGGER_CHANNEL;
  protected string $level = self::LOGGER_LEVEL;

  public function __construct(string $channel = self::LOGGER_CHANNEL, LoggerIntent $level = LoggerIntent::DEBUG) {
    if (!empty($channel)) $this->channel = $channel;
    if (!empty($level)) $this->level = $level->value;
  }

  public static function l(string $channel = self::LOGGER_CHANNEL, LoggerIntent $level = LoggerIntent::DEBUG, string $message = '', array $context = []): void {
    $innerLogger = new self(
      channel: $channel,
      level: $level,
    );
    $logger = Drupal::logger($innerLogger->level);

    match($innerLogger->level) {
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
