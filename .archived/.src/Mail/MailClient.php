<?php

namespace Drupal\pingvin\Mail;

use Drupal;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Exception;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

class MailClient {

  protected string $themeKey;
  protected array $themeParams;

  public function __construct(string $themeKey, array $themeParams) {
    $this->themeKey = $themeKey;
    $this->themeParams = $themeParams;
  }
  public function sendMail(string $from, string $to, string $subject, string $langcode = 'en'): void {
    /* @var MailManagerInterface $mailManager */
    $mailManager = Drupal::service('plugin.manager.mail');

    $params = [
      'headers' => [
        'Content-Type' => 'text/html; charset=UTF-8;',
        'Content-Transfer-Encoding' => '8bit',
      ],
      'from' => $from,
      'subject' => $subject,
      'body' => [
        '#theme' => $this->themeKey,
        '#params' => $this->themeParams,
      ],
    ];

    $mailManager->mail(pw8dr1_PROJECT_ID, 'send_mail', $to, $langcode, $params, null, true);
  }

  public function sendMailWithAttachments(string $from, string $to, string $subject, array $attachments = [], string $langcode = 'en'): bool {
    /* @var MailManagerInterface $mailManager */
    $mailManager = Drupal::service('plugin.manager.mail');

    $mailAttachments = [];
    if (!empty($attachments)) {
      foreach ($attachments as $attachment) {
        if (empty($attachment['filePath'])) {
          throw new Exception('Attachment filepath is required.');
        }
        if (empty($attachment['fileName'])) {
          throw new Exception('Attachment filename is required.');
        }

        $mailAttachments[] = [
          'filename' => $attachment['fileName'],
          'filepath' => $attachment['filePath'],
          'filemime' => Drupal::service('file.mime_type.guesser')->guessMimeType($attachment['filePath']),
        ];
      }
    }

    $params = [
      'headers' => [
        'Content-Type' => 'text/html; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
        'From' => $from,
      ],
      'subject' => $subject,
      'body' => [
        '#theme' => $this->themeKey,
        '#params' => $this->themeParams,
      ],
      'attachments' => $mailAttachments,
    ];

    $mailManager->mail(pw8dr1_PROJECT_ID, 'send_mail_with_attachments', $to, $langcode, $params, null, true);
    return true;
  }
}
