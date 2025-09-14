<?php

namespace Drupal\drift_eleven\EventSubscriber;

use Drupal;
use Drupal\drift_eleven\Core\Cache\Cache;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Middleware\MiddlewareHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RouteMiddlewareControlSubscriber implements EventSubscriberInterface {
  protected const int PRIORITY = 998;

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

    // Drift Eleven route exists, now we can apply the middlewares if any
    $routeMiddlewares = $route['useMiddleware'] ?: [];
    if (!empty($routeMiddlewares)) {
      $response = new MiddlewareHandler($request, $route, $routeMiddlewares)->handle();
      if ($response instanceof Reply) {
        $event->setResponse($response);
        $event->stopPropagation();
      }
    }

    // all ok, we continue with the request
    // normally with the endpoint controller
  }
}
