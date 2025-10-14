<?php

namespace Drupal\drift_eleven\Routes;


use Drupal\block_content\Entity\BlockContent;
use Drupal\drift_eleven\Core\Content\Field\Resolver\FieldResolver;
use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\drift_eleven\Core\Http\Route\Interface\RouteHandlerInterface;

/**
 * **Example Route Definition**
 * - todo add description
 * @route
 * id= 'drift_eleven:example'
 * name= 'Drift Eleven Example Route'
 * method= 'GET'
 * description= 'An Example Drift Eleven Route'
 * path= 'example/route/{random_number}'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['request', 'auth']
 * useCache= true
 * @route-end
 */
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
