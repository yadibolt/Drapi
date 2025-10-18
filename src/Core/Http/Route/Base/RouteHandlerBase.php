<?php

namespace Drupal\drift_eleven\Core\Http\Route\Base;

use Drupal;
use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Interface\RouteHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class RouteHandlerBase implements RouteHandlerInterface {
  protected Request $currentRequest;
  protected string $routeId;
  protected array $context = [];
  protected array $cacheTags = [];
  protected string $userAgent = 'unknown';
  protected string $clientIp = '';
  protected array $queryParams = [];
  protected array $data = [];
  protected array $files = [];

  public function handle(): Reply {
    return Reply::make([
      'message' => 'NOT IMPLEMENTED',
    ], 501);
  }
  public function init(Request $request): Reply {
    $this->currentRequest = $request;
    $this->routeId = $request->attributes->get('_route');
    $this->context = $request->attributes->get('context', []);
    $this->userAgent = $request->headers->get('User-Agent', 'unknown');
    $this->clientIp = $request->getClientIp() ?? '';
    $this->queryParams = $request->query->all();

    if ($this->currentRequest->headers->has('Content-Type') && str_contains($this->currentRequest->headers->get('Content-Type'), 'application/json')) {
      $this->data = json_decode($this->currentRequest->getContent(), true) ?: [];
    }

    if ($this->currentRequest->headers->has('Content-Type') && str_contains($this->currentRequest->headers->get('Content-Type'), 'multipart/form-data')) {
      $this->data = $this->currentRequest->request->all() ?: [];
      $this->files = $this->currentRequest->files->all() ?: [];
    }

    return $this->handle();
  }
  public function setCacheTags(array $tags): void {
    $configuration = Drupal::configFactory()->getEditable(ROUTE_CONFIG_NAME_DEFAULT);
    $routeRegistry = $configuration->get('route_registry') ?: [];

    if (!$this->routeId) return;
    if (!isset($routeRegistry[$this->routeId])) return;

    $currentCacheTags = $routeRegistry[$this->routeId]['cache_tags'] ?? [];
    $mergedTags = array_unique(array_merge($currentCacheTags, $tags));
    $routeRegistry[$this->routeId]['cache_tags'] = $mergedTags;
    $this->cacheTags = $mergedTags;

    $configuration->set('route_registry', $routeRegistry);
    $configuration->save();
  }

  protected function getRequestLangcode(): string {
    return $this->context['request']['langcode'] ?? 'en';
  }
  protected function getUriToken(string $token): ?string {
    return $this->currentRequest->get($token);
  }
  protected function getRequestData(): array {
    return $this->data;
  }
  protected function getMiddlewareContext(): array {
    return $this->context;
  }
  protected function getQueryParams(): array {
    return $this->queryParams;
  }
}
