<?php

namespace Drupal\drift_eleven\Core\Routes\Content;

use Drupal;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;

/**
 * @route
 * id= 'drift_eleven:content:block'
 * name= 'Drift Eleven Example Route'
 * method= 'GET'
 * description= 'Api Block for content'
 * path= 'api/block/{machine_name}'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_anonym', 'request']
 * useCache= false
 * @route-end
 */
class BlockRoute extends RouteFoundation {
  public function handle(): Reply {
    // $this->setCacheTags([]);

    $langcode = $this->context['request']['langcode'];
    $block_machine_name = $this->request->get('machine_name');
    $block = BlockContent::load($block_machine_name);

    if ($block->hasTranslation($langcode)) $block = $block->getTranslation($langcode);

    // imitate field resolver
    $resolvedFields = [];

    $fields = $block->getFields();
    $fieldResolver = new Drupal\drift_eleven\Core\Resolver\FieldResolver();
    $resolvedFields = $fieldResolver->setFields($fields, [
      'customFieldsOnly' => true,
      'includeProtectedFields' => false,
      'loadEntities' => true,
    ])->resolveFields();

    /*foreach ($block->getFields() as $fieldName => $field) {
      // just for now
      $fieldDef = $field->getFieldDefinition();
      $resolvedFields[$fieldName] = [$fieldDef->getSetting('target_type'), $fieldDef->getType(), $field->getValue()];

      /*if ($fieldName === 'field_slideshow_images') {
        $items = [];
        foreach ($field->getValue() as $item) {
          $file = Drupal\file\Entity\File::load($item['target_id']);
          if ($file) {
            $items[] = [
              'id' => $file->id(),
              'url' => $file->createFileUrl(false),
              'title' => $file->getFilename(),
              'alt' => $item['alt'] ?? '',
            ];
          }
        }
        $resolvedFields[$fieldName] = $items;
      }
    }*/
    // end imitate field resolver

    return new Reply([
      'message' => 'Block route',
      'fields' => $resolvedFields,
    ], 200);
  }
}
