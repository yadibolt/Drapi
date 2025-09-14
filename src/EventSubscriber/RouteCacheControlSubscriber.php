<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Drupal;
use Drupal\drift_eleven\Core\Auth\JsonWebToken;
use Drupal\drift_eleven\Core\Auth\JsonWebTokenInterface;
use Drupal\drift_eleven\Core\Cache\Cache;
use Drupal\drift_eleven\Core\HTTP\Reply;
use Drupal\drift_eleven\Core\Middleware\MiddlewareInterface;
use Drupal\drift_eleven\Core\Session\Session;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RouteCacheControlSubscriber implements EventSubscriberInterface {
  protected const int PRIORITY = 999;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', self::PRIORITY],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function onKernelRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) return;

    $route = null;
    $request = $event->getRequest();
    $requestUri = $request->getPathInfo();
    $requestUriSplit = mb_split('/', ltrim($requestUri, '/'));

    $cacheHit = Cache::find(D9M7_CACHE_KEY . ":url:$requestUri");
    if (!$cacheHit) return;

    $config = Drupal::configFactory()->getEditable(D9M7_CONFIG_KEY);
    $routeRegistry = $config->get('routeRegistry') ?: [];

    foreach ($routeRegistry as $rt) {
      $pathSplit = mb_split('/', $rt['path']);

      // if the number of segments do not match, continue
      if (count($pathSplit) !== count($requestUriSplit)) continue;

      for ($i = 0; $i < count($pathSplit); $i++) {
        // url param check
        if (str_starts_with($pathSplit[$i], '{') && str_ends_with($pathSplit[$i], '}')) continue;

        // if the url does not match, continue to next route
        if ($pathSplit[$i] !== $requestUriSplit[$i]) continue 2;
      }

      // urls match
      $route = $rt;
      break;
    }

    if (!$route) return;

    // check access
    if (
      (empty($route['roles'])) &&
      (empty($route['permissions']) || (count($route['permissions']) === 1) && $route['permissions'][0] === 'access content') &&
      (empty($route['useMiddleware']) || (!in_array(MiddlewareInterface::AUTH, $route['useMiddleware'])))
    ) {
      // if there are no required roles and no required permissions and no required auth
      // we set the response and send it
      $event->setResponse(
        new Reply($cacheHit['data'], $cacheHit['status'], $cacheHit['headers'], true)
      );
      $event->stopPropagation();
    } else {
      // we try to identify the user before all the middlewares
      // so the response is a little bit faster
      $jsonWebToken = new JsonWebToken();
      $req = $event->getRequest();
      $authHeader = $req->headers->get('authorization');

      if (empty($authHeader) || !preg_match('/^Bearer\s+(\S+)$/', $authHeader, $matches)) return;

      $isOk = $jsonWebToken->validate($matches[1]);
      if (!$isOk->valid || $isOk->expired || $isOk->error) return;

      $payload = JsonWebToken::payloadFrom($matches[1]);
      if (!isset($payload['userId']) || !is_numeric($payload['userId']) || $payload['userId'] < 0) return;

      // first, we check the cache
      // if there is token stored in the cache
      // the session exists, because the token was verified,
      // and we do not need to query the database
      $hit = Cache::find(D9M7_CACHE_KEY . ":session:$matches[1]");
      if ($hit) {
        if (!empty($hit['roles'])) {
          $s1 = sort($hit['roles']);
          $s2 = sort($route['roles']);

          if ($s1 !== $s2) return;
        }

        if (!empty($hit['permissions'])) {
          $s1 = sort($hit['permissions']);
          $s2 = sort($route['permissions']);

          if ($s1 !== $s2) return;
        }

        $event->setResponse(
          new Reply($cacheHit['data'], $cacheHit['status'], $cacheHit['headers'], true)
        );
        $event->stopPropagation();
      }

      // there is no cached session
      // we have to query it
      $sessionUser = Session::findUser($matches[1]);
      if (!$sessionUser) return;

      if (!$sessionUser->isActive()) return;

      $userRoles = $sessionUser->getRoles();
      $s1 = sort($route['roles']);
      $s2 = sort($userRoles);
      if ($s1 !== $s2) return;

      $userPermissions = $sessionUser->getPermissions();
      $s1 = sort($route['permissions']);
      $s2 = sort($userPermissions);
      if ($s1 !== $s2) return;

      // all ok
      // we can return the response and set the cache for the future hits
      Cache::make(D9M7_CACHE_KEY . ":session:$matches[1]", $sessionUser->getCacheStructData());
      $event->setResponse(
        new Reply($cacheHit['data'], $cacheHit['status'], $cacheHit['headers'], true)
      );
      $event->stopPropagation();
    }
  }
}
