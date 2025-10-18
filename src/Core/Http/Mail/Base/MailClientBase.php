<?php

namespace Drupal\drapi\Core\Http\Mail\Base;

use Drupal;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\File\MimeType\MimeTypeGuesser;
use Drupal\Core\Mail\MailManager;
use Exception;

abstract class MailClientBase {
  protected MailManager $mailManager;
  protected string $moduleName;
  protected string $contentType = 'text/html; charset=UTF-8;';
  protected string $contentTransferEncoding = '8bit';
  protected string $from;
  protected string $to;
  protected string $subject;
  protected string $themeKey;
  protected array $themeParams = [];
  protected array $attachments = [];
  protected string $langcode = 'en';
  protected bool $reply = false;
  protected bool $send = true;

  /**
   * @throws PluginNotFoundException
   */
  public function __construct(string $moduleName, string $from, string $to, string $subject, string $themeKey) {
    if (!Drupal::moduleHandler()->moduleExists('mailsystem')) {
      throw new PluginNotFoundException('mailsystem', 'The "mailsystem" module is required.');
    }
    if (!Drupal::moduleHandler()->moduleExists('mimemail')) {
      throw new PluginNotFoundException('mimemail', 'The "mimemail" module is required.');
    }

    $this->mailManager = Drupal::service('plugin.manager.mail');
    $this->moduleName = $moduleName;
    $this->from = $from;
    $this->to = $to;
    $this->subject = $subject;
    $this->themeKey = $themeKey;
  }

  public function sendMail(): bool {
    $headers = [
      'Content-Type' => $this->contentType,
      'Content-Transfer-Encoding' => $this->contentTransferEncoding,
    ];

    $body = [
      '#theme' => $this->themeKey,
      '#params' => $this->themeParams,
    ];

    $mailParameters = [
      'headers' => $headers,
      'from' => $this->from,
      'subject' => $this->subject,
      'body' => $body,
      'attachments' => $this->attachments,
    ];

    try {
      $this->mailManager->mail(
        $this->moduleName,
        'send_mail',
        $this->to,
        $this->langcode,
        $mailParameters,
        $this->reply,
        $this->send
      );
    } catch (Exception) {
      return false;
    }

    return true;
  }

  public function addAttachment(string $filename, string $fileuri, ?string $filemime = null): self {
    /** @var MimeTypeGuesser $mimeGuesserService */
    $mimeGuesserService = Drupal::service('file.mime_type.guesser');
    $filepath = Drupal::service('file_system')->realpath($fileuri);

    if (!file_exists($filepath)) return $this;

    $this->attachments[] = [
      'filename' => $filename,
      'filepath' => $filepath,
      'filemime' => $filemime ?? $mimeGuesserService->guessMimeType($fileuri),
    ];

    return $this;
  }

  public function setFrom(string $from): self {
    $this->from = $from;
    return $this;
  }
  public function setTo(string $to): self {
    $this->to = $to;
    return $this;
  }
  public function setSubject(string $subject): self {
    $this->subject = $subject;
    return $this;
  }
  public function setThemeKey(string $themeKey): self {
    $this->themeKey = $themeKey;
    return $this;
  }
  public function setThemeParams(array $params): self {
    $this->themeParams = $params;
    return $this;
  }
  public function setAttachments(array $attachments): self {
    $this->attachments = $attachments;
    return $this;
  }
  public function setLangcode(string $langcode): self {
    $this->langcode = $langcode;
    return $this;
  }
  public function setReply(bool $reply): self {
    $this->reply = $reply;
    return $this;
  }
  public function setSend(bool $send): self {
    $this->send = $send;
    return $this;
  }
}
