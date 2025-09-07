<?php

namespace Drupal\pingvin\Resolver;

use Drupal;
use Drupal\Core\Entity\EntityInterface;

class PathResolver {
  public static function entityFromAlias(string $alias, string $langcode): ?array {
    /** @var Drupal\path_alias\AliasManager $internalPath */
    $internalPath = Drupal::service('path_alias.manager')->getPathByAlias($alias, $langcode);

    /** @var Drupal\Core\Routing\Router $router */
    $router = \Drupal::service('router.no_access_checks');
    $params = $router->match($internalPath);

    foreach ($params as $param) {
      if ($param instanceof EntityInterface) {
        return [
          'entityType' => $param->getEntityType()->id(),
          'entityId' => $param->id(),
        ];
      }
    }

    return null;
  }
}
