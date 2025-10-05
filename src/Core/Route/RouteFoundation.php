<?php

namespace Drupal\drift_eleven\Core\Route;

use Drupal;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Symfony\Component\HttpFoundation\Request;

abstract class RouteFoundation implements RouteFoundationInterface {
  /**
   * Request object from Symfony
   * @var Request $request
   */
  protected Request $request;
  /**
   * Context array from request attributes that middlewares can populate
   * @var array $context
   */
  protected array $context = [];
  /**
   * Cache tags to add to the response
   * @var array $cacheTags
   */
  protected array $cacheTags = [];
  /**
   * User agent string from request headers
   * @var string $userAgent
   */
  protected string $userAgent = 'unknown';
  /**
   * Hostname from request headers
   * @var string $hostname
   */
  protected string $hostname = '';

  /**
   * Query parameters from the request URL
   * @var array $queryParams
   */
  protected array $queryParams = [];
  /**
   * Data array parsed from request body (JSON or form-data)
   * @var array $data
   */
  protected array $data = [];
  /**
   * Files array parsed from request body (form-data)
   * @var array $files
   */
  protected array $files = [];

  /**
   * Initializes the route with the request and context.
   *
   * @param Request $request the HTTP request object from Symfony.
   * @return Reply the HTTP response object.
   */
  public function init(Request $request): Reply {
    $this->request = $request;
    $this->context = $request->attributes->get('context', []);
    $this->userAgent = substr($this->request->server->get('HTTP_USER_AGENT') ?: 'unknown', 0, 512);
    $this->hostname = $this->getClientIp($request);
    $this->queryParams = $this->request->query->all() ?: [];

    if ($this->request->headers->has('Content-Type') && str_contains($this->request->headers->get('Content-Type'), 'application/json')) {
      $this->data = json_decode($this->request->getContent(), true) ?: [];
    }

    if ($this->request->headers->has('Content-Type') && str_contains($this->request->headers->get('Content-Type'), 'form-data')) {
      $this->data = $this->request->request->all() ?: [];
      $this->files = $this->request->files->all() ?: [];
    }

    return $this->handle();
  }

  /**
   * Gets the client IP address from the request.
   *
   * @param Request $request The HTTP request object.
   * @return string The client's IP address.
   */
  protected function getClientIp(Request $request): string {
    if ($request->server->has('HTTP_X_FORWARDED_FOR')) {
      $ipList = explode(',', $request->server->get('HTTP_X_FORWARDED_FOR'));
      return trim($ipList[0]);
    }

    if ($request->server->has('HTTP_CLIENT_IP')) {
      return $request->server->get('HTTP_CLIENT_IP');
    }

    return $request->server->get('REMOTE_ADDR') ?: '0.0.0.0';
  }

  /**
   * Handles the route logic. This method must be overridden in classes extending RouteFoundation.
   *
   * @return Reply the HTTP response object.
   */
  public function handle(): Reply {
    return new Reply([
      'message' => 'This has to be overridden!',
    ], 501);
  }

  public function setCacheTags(array $tags): void {
    $configCtx = D9M7_PROJECT_ID . '.settings';
    $config = Drupal::configFactory()->getEditable($configCtx);
    $routeRegistry = $config->get('routeRegistry') ?: [];

    $routeId = $this->request->attributes->get('_route');
    if (isset($routeId) && is_string($routeId)) {
      $routeRegistry[$routeId]['cacheTags'] = array_unique(array_merge($this->cacheTags, $tags));
      $config->set('routeRegistry', $routeRegistry);
      $config->save();
    }

    Drupal\drift_eleven\Core\Logger\Logger::l('ran', [], 'info');

    $this->cacheTags = array_unique(array_merge($this->cacheTags, $tags));
  }
}
