<?php

namespace Drupal\drift_eleven\Core\Http\Mail;

use Drupal\drift_eleven\Core\Http\Mail\Base\MailClientBase;

class MailClient extends MailClientBase {
  public static function make(string $moduleName, string $from, string $to, string $subject, string $themeKey, array $themeParams = [], array $attachments = [], string $langcode = 'en', bool $reply = false, bool $send = true): MailClient {
    $instance = new self($moduleName, $from, $to, $subject, $themeKey);

    if (!empty($themeParams)) $instance->setThemeParams($themeParams);
    if (!empty($attachments)) $instance->setAttachments($attachments);
    $instance->setLangcode($langcode);
    $instance->setReply($reply);
    $instance->setSend($send);

    return $instance;
  }
}
