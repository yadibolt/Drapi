<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Drupal;
use Drupal\drift_eleven\Core\Auth\JWT;
use Drupal\drift_eleven\Core\Cache\Cache;
use Drupal\drift_eleven\Core\Cache\Enum\CacheIntent;
use Drupal\drift_eleven\Core\Http\Middleware\AuthMiddleware;
use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Session\Enum\SubjectIntent;
use Drupal\drift_eleven\Core\Session\Session;
use Drupal\drift_eleven\Core\Session\Subject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CacheControl implements EventSubscriberInterface{
  protected const int PRIORITY = 999;

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', self::PRIORITY],
    ];
  }

  public function onKernelRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) return;

    $request = $event->getRequest();
    $method = $request->getMethod();

    if (strtolower($method) === 'get') {
      $userToken = '';

      $authorizationHeader = $request->headers->get('authorization');
      if (!empty($authorizationHeader) && preg_match('/^Bearer\s+(\S+)$/', $authorizationHeader, $matches)) {
        $userToken = $matches[1] ?? '';
      }

      $cacheIdentifier = $request->getRequestUri();
      if (!empty($userToken)) $cacheIdentifier .= ROUTE_CACHE_TOKEN_ADDER_DEFAULT . $userToken;

      $cache = Cache::make();
      $cacheHit = $cache->get($cacheIdentifier, CacheIntent::URL);
      if (empty($cacheHit)) return;

      $configuration = Drupal::configFactory()->get(ROUTE_CONFIG_NAME_DEFAULT);
      $routeRegistry = $configuration->get('route_registry') ?? [];
      $routeRef = null;
      $uriParts = mb_split('/', ltrim($request->getRequestUri(), '/'));

      foreach ($routeRegistry as $route) {
        if (isset($route['path'])) continue;

        $parts = mb_split('/', $route['path']);
        if (count($parts) !== count($uriParts)) continue;

        for ($i = 0; $i < count($parts); $i++) {
          if (str_starts_with($parts[$i], '{') && str_ends_with($parts[$i], '}')) continue;
          if ($parts[$i] !== $uriParts[$i]) continue 2;
        }

        $routeRef = $route;
      }

      if (empty($routeRef)) return;

      $rolesEmpty = empty($route['roles']);
      $permissionsEmpty = empty($route['permissions']) || ((count($route['permissions']) === 1) && $route['permissions'][0] === 'access content');
      $middlewareEmpty = empty($route['useMiddleware']) || !in_array(AuthMiddleware::getId(), $route['use_middleware']);

      if ($rolesEmpty && $permissionsEmpty && $middlewareEmpty) {
        $cacheHit = $this->createCachedResponse($cache, $cacheIdentifier, $cacheHit);
        if ($cacheHit === null) return;

        $event->setResponse(
          Reply::make($cacheHit['data'], $cacheHit['status']. $cacheHit['headers'])
        );
        $event->stopPropagation();
      } else {
        if (empty($userToken)) return;

        $checked = JWT::check($userToken);
        if (!$checked->isValid() || $checked->isExpired() || $checked->hasError()) return;

        $payload = JWT::payloadFrom($userToken);
        if (!$this->checkPayload($payload)) return;

        if ($payload['data']['type'] === SubjectIntent::ANONYMOUS) return;

        $subject = Session::make($userToken)->find()?->getSubject();
        if (!$subject) return;
        if (!$subject->isActive()) return;
        if (!$this->checkRequirements($subject, $routeRef)) return;

        $cacheHit = $this->createCachedResponse($cache, $cacheIdentifier, $cacheHit);
        if ($cacheHit === null) return;

        $event->setResponse(
          Reply::make($cacheHit['data'], $cacheHit['status']. $cacheHit['headers'])
        );
        $event->stopPropagation();
      }
    }
  }

  protected function getCachedHeaders(ResponseHeaderBag $headers, int $duration): ResponseHeaderBag {
    $headers->set('Cache-Control', 'public, max-age=' . $duration);
    $headers->set(HTTP_HEADER_CACHEABLE_NAME_DEFAULT, HTTP_HEADER_CACHEABLE_DEFAULT);
    $headers->set(HTTP_HEADER_CACHE_NAME_DEFAULT, HTTP_HEADER_CACHED_DEFAULT);
    $headers->set(HTTP_HEADER_CACHE_HIT_NAME_DEFAULT, HTTP_HEADER_CACHE_HIT_DEFAULT);
    $headers->set('Pragma', 'cache');
    $headers->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');

    return $headers;
  }
  protected function checkRequirements(Subject $subject, array $route): bool {
    $routePermissions = $route['permissions'] ?? [];
    $routeRoles = $route['roles'] ?? [];

    $permissions = $subject->getPermissions();
    if (array_any($routePermissions,fn($routePermission) => !in_array($routePermission, $permissions))) {
      return false;
    }

    $roles = $subject->getRoles();
    if (array_any($routeRoles, fn($routeRole) => !in_array($routeRole, $roles))) {
      return false;
    }

    return true;
  }
  protected function checkPayload(array $payload): bool {
    if (empty($payload)) return false;
    if (!isset($payload['data']))

    if (!isset($payload['data']['user_id'])) return false;
    if (!is_numeric($payload['data']['user_id'])) return false;
    if ((int)$payload['data']['user_id'] <= 0) return false;

    if (!isset($payload['data']['type'])) return false;
    if (!is_string($payload['data']['type'])) return false;
    if ($payload['data']['type'] !== SubjectIntent::AUTHENTICATED) return false;

    return true;
  }
  protected function createCachedResponse(Cache $cache, string $cacheIdentifier, array $cacheHit): ?array {
    if ($cacheHit['headers_replaced'] === false) {
      if ($cacheHit['headers'] instanceof ResponseHeaderBag) {
        $cacheHit['headers'] = $this->getCachedHeaders($cacheHit['headers'], $cache->getCacheDuration());
      }

      $cacheTags = [];
      if (!empty($this->route['cache_tags']) && is_array($this->route['cache_tags'])) {
        $cacheTags = $this->route['cache_tags'] ?? [];
      }

      $cacheHit['data']['timestamp'] = time();
      $cacheHit['headers_replaced'] = true;

      $cache->create($cacheIdentifier, CacheIntent::URL, $cacheHit, $cacheTags);

      return $cacheHit;
    }

    return null;
  }
}
