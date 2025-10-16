<?php

namespace Drupal\drift_eleven\Routes;


use Drupal\block_content\Entity\BlockContent;
use Drupal\drift_eleven\Core\Content\Field\Resolver\FieldResolver;
use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandler;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drift_eleven\Core\Http\Route\Interface\RouteHandlerInterface;

#[RouteHandler(
  id: 'drift_eleven:example',
  name: 'Drift Eleven Example Route',
  method: 'GET',
  path: 'example/route/{random_number}',
  description: 'An Example Drift Eleven Route',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request', 'auth'],
  useCache: true
)]
class ExampleRoute extends RouteHandlerBase implements RouteHandlerInterface {
  public function handle(): Reply {
    $this->setCacheTags(['block_content:1']);

    $content = null;
    $blockId = $this->getUriToken('random_number');
    if ($blockId) {
      $block = BlockContent::load($blockId);
      $blockFields = $block->getFields();

      $content = FieldResolver::make($blockFields, [
        'load_entities' => true,
        'load_custom' => true,
        'load_protected' => false,
      ])->resolve();
    }

    return Reply::make([
      'message' => 'No language set',
      'fields' => $content,
    ], 200);
  }
}
