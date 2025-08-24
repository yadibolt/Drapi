<?php

namespace Drupal\pingvin\Mail;

use Drupal;
use Drupal\Core\Mail\MailManagerInterface;

class MailClient {

  protected string $themeKey;
  protected array $themeParams;

  public function __construct(string $themeKey, array $themeParams) {
    $this->themeKey = $themeKey;
    $this->themeParams = $themeParams;
  }
  public function sendMail(string $from, string $to, string $subject, string $langcode): void {
    /* @var MailManagerInterface $mailManager */
    $mailManager = Drupal::service('plugin.manager.mail');

    $params = [
      'headers' => [
        'Content-Type' => 'text/html; charset=UTF-8;',
        'Content-Transfer-Encoding' => '8Bit',
      ],
      'from' => $from,
      'subject' => $subject,
      'body' => [
        '#theme' => $this->themeKey,
        '#params' => $this->themeParams,
      ],
    ];

    $mailManager->mail(pw8dr1_PROJECT_ID, 'send_mail', $to, $langcode, $params, null);
  }

  public static function sendMailWithAttachments(): bool {
    // todo
    return true;
  }
}
