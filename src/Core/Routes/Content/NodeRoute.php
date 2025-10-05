<?php

namespace Drupal\drift_eleven\Core\Routes\Content;

use Drupal;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Resolver\FieldResolver;
use Drupal\drift_eleven\Core\Route\RouteFoundation;
use Drupal\path_alias\Entity\PathAlias;
use Symfony\Component\Filesystem\Path;

/**
 * @route
 * id= 'drift_eleven:content:node'
 * name= 'Drift Eleven Example Route'
 * method= 'GET'
 * description= 'Api Node for content'
 * path= 'api/content'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_anonym', 'request']
 * useCache= false
 * @route-end
 */
class NodeRoute extends RouteFoundation {
  public function handle(): Reply {
    $destination = !empty($this->queryParams['destination']) ? $this->queryParams['destination'] : '';

    $entityPathResolver = new Drupal\drift_eleven\Core\Resolver\EntityPathResolver();
    $entity = $entityPathResolver->setDestination($destination, [])->resolve();

    if (!$entity) {
      return new Reply([
        'message' => 'Entity not found',
      ], 404);
    }

    $fieldResolver = new FieldResolver();
    $fields = $fieldResolver->setFields($entity->getFields(), [
      'customFieldsOnly' => true,
      'includeProtectedFields' => false,
      'loadEntities' => true,
    ])->resolveFields();

    return new Reply([
      'message' => 'Node route',
      'fields' => $fields,
    ], 200);
  }
}
