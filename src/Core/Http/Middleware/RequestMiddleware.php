<?php

namespace Drupal\drapi\Core\Http\Middleware;

use Drupal\drapi\Core\Http\Enum\ReplyIntent;
use Drupal\drapi\Core\Http\Middleware\Base\MiddlewareBase;
use Drupal\drapi\Core\Http\Middleware\Interface\MiddlewareInterface;
use Drupal\drapi\Core\Http\Reply;

class RequestMiddleware extends MiddlewareBase implements MiddlewareInterface {
  public static function make(): self {
    return new self();
  }
  public static function getId(): string {
    return 'request';
  }
  public function process(): ?Reply {
    $languageHeader = $this->currentRequest->headers->get('Accept-Language', 'en');
    $contentTypeHeader = $this->currentRequest->headers->get('Content-Type', 'application/json');

    if (strtolower($this->currentRequest->getMethod()) === 'post') {
      if ($contentTypeHeader !== 'application/json' && $contentTypeHeader !== 'multipart/form-data') {
        return Reply::make(
          data: [
            'action_id' => ReplyIntent::INVALID_CONTENT_TYPE,
            'message' => 'Content-Type must be application/json or multipart/form-data.',
          ], status: 415
        );
      }
    }

    $requestContext = $this->currentRequest->attributes->get('context', []);
    $this->addAttributes($this->currentRequest, 'context', [
      ...$requestContext,
      'request' => [
        'langcode' => $languageHeader,
      ]
    ]);

    return null;
  }
}
