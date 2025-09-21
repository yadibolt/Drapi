<?php

namespace Drupal\drift_eleven\Core\Routes\Content;

use Drupal;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\drift_eleven\Core\HTTP\Response\Reply;
use Drupal\drift_eleven\Core\Route\RouteFoundation;

/**
 * @route
 * id= 'drift_eleven:content:bundle'
 * name= 'Drift Eleven Example Route'
 * method= 'GET'
 * description= 'Api Bundle for content'
 * path= 'api/bundle'
 * permissions= ['access content']
 * roles= []
 * useMiddleware= ['auth_anonym', 'request']
 * useCache= true
 * @route-end
 */
class BundleRoute extends RouteFoundation {
  public function handle(): Reply {
    // explicitly say what tags should invalidate this response
    $this->setCacheTags(['menu_link_content']);

    $menuName = 'main';
    $menuTree = Drupal::menuTree();

    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth(5);
    $parameters->onlyEnabledLinks();

    $tree = $menuTree->load($menuName, $parameters);
    $manipulatedTree = $menuTree->transform($tree, [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ]);

    $menuData = $this->processMenuTree($manipulatedTree);

    return new Reply([
      'message' => 'Bundle route',
      'menu' => $menuData,
    ], 200);
  }

  private function processMenuTree(array $tree): array {
    $items = [];

    foreach ($tree as $_ => $item) {
      $menuItem = [
        'title' => $item->link->getTitle(),
        'url' => $item->link->getUrlObject()->toString(),
        'id' => $item->link->getPluginId(),
        'weight' => $item->link->getWeight(),
      ];

      if ($item->hasChildren) {
        $menuItem['children'] = $this->processMenuTree($item->subtree);
      }

      $items[] = $menuItem;
    }

    return $items;
  }
}
