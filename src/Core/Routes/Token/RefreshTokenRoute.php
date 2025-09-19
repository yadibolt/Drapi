<?php

namespace Drupal\drift_eleven\Core\Routes\Token;

use Drupal\drift_eleven\Core\Auth\JsonWebToken;
use Drupal\drift_eleven\Core\Auth\JsonWebTokenInterface;
use Drupal\drift_eleven\Core\Cache\Cache;
use Drupal\drift_eleven\Core\Cache\CacheInterface;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\HTTP\Response\ReplyInterface;
use Drupal\drift_eleven\Core\Logger\Logger;
use Drupal\drift_eleven\Core\Logger\LoggerInterface;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\drift_eleven\Core\Session\Session;
use Drupal\drift_eleven\Core\Session\SessionUser;

/**
 * @route
 * id= 'drift_eleven:token:refresh'
 * name= 'Drift Eleven - Refresh token route'
 * method= 'GET'
 * description= 'Refresh token route for Drift Eleven.'
 * path= 'auth/refresh-token'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_refresh', 'request']
 * @route-end
 */
class RefreshTokenRoute extends RouteFoundation {
  public function handle(): Reply {
    Session::invalidate($this->context['refreshToken'], JsonWebTokenInterface::TOKEN_REFRESH);
    $jsonWebToken = new JsonWebToken();
    $payload = [
      'userId' => $this->context['user']['id'],
    ];

    $refreshTok = Session::find($this->context['refreshToken']);
    if (!$refreshTok) return new Reply([
      'message' => 'Could not create session.',
      'actionId' => ReplyInterface::ACTION_COULD_NOT_CREATE_SESSION,
    ], 500);

    $accessTok = $jsonWebToken->make(JsonWebTokenInterface::TOKEN_ACCESS, $payload);
    $accessTokId = new Session(
      $this->context['user']['id'],
      $accessTok,
      JsonWebTokenInterface::TOKEN_ACCESS,
      $refreshTok->id,
      $this->userAgent,
      $this->hostname,
    )->save();

    if (!$accessTokId) return new Reply([
      'message' => 'Could not create session.',
      'actionId' => ReplyInterface::ACTION_COULD_NOT_CREATE_SESSION,
    ], 500);

    $sessionUser = new SessionUser(
      $this->context['user']['id'],
      true,
      $this->context['user']['roles'],
      $this->context['user']['permissions'],
      $this->context['user']['langcode'],
    );

    $cacheName = D9M7_CACHE_KEY . ":session_" . $accessTok;
    Cache::make($cacheName, $sessionUser->getCacheStructData());

    Logger::l('User with id @userId has refreshed their access token.', [
      '@userId' => $this->context['user']['id'],
    ], LoggerInterface::LEVEL_INFO);

    return new Reply([
      'message' => 'Token generated.',
      'accessToken' => $accessTok,
    ], 200);
  }
}
