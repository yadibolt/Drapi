<?php

namespace Drupal\drift_eleven\Core\Session;

use Drupal\drift_eleven\Core\Auth\JsonWebTokenInterface;

interface SessionInterface {
  public function __construct(int $entityId, string $token, string $tokenType, int $tokenParentId, string $userAgent = '', string $hostname = '', ?int $id = null, int $updatedAt = 0, int $createdAt = 0);
  public function save(): int;
  public static function find(string $token): bool|object|null;
  public static function findUser(string $token, string $tokenType = JsonWebTokenInterface::TOKEN_ACCESS): ?SessionUser;
  public static function delete(string $token, string $tokenType = JsonWebTokenInterface::TOKEN_ACCESS): bool;
}
