<?php

namespace Drupal\Mymodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\system\Entity\Menu;

/**
 * Provides a 3rd Level Menu block.
 *
 * @Block(
 *   id = "mymodule_third_level_menu",
 *   admin_label = @Translation("3rd Level Menu block"),
 *   category = "Menus"
 * )
 */
class ThirdLevelMenuBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $title = '';
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      $title = $node->getTitle();
    }
    elseif ($term = \Drupal::routeMatch()->getParameter('taxonomy_term')) {
      $title = $term->getName();
    }

    $menu = Menu::load('universes-menu');
    $level = 3;
    $depth = 1;

    $menu_name = $menu->id();
    $container = \Drupal::getContainer();
    /** @var \Drupal\Core\Menu\MenuLinkTreeInterface $menuTree */
    $menuTree = $container->get('menu.link_tree');
    $container->get('menu.active_trail');
    $parameters = $menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    $parameters->setMinDepth($level);
    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $menuTree->maxDepth()));
    }

    // For menu blocks with start level greater than 1, only show menu items
    // from the current active trail. Adjust the root according to the current
    // position in the menu in order to determine if we can show the subtree.
    if ($level > 1) {
      if (count($parameters->activeTrail) >= $level) {
        // Active trail array is child-first. Reverse it, and pull the new menu
        // root based on the parent of the configured start level.
        $menu_trail_ids = array_reverse(array_values($parameters->activeTrail));
        $menu_root = $menu_trail_ids[$level - 1];
        $parameters->setRoot($menu_root)->setMinDepth(1);
        if ($depth > 0) {
          $parameters->setMaxDepth(min($level - 1 + $depth - 1, $menuTree->maxDepth()));
        }
      }
      else {
        return [];
      }
    }
    $tree = $menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menuTree->transform($tree, $manipulators);

    $build = $menuTree->build($tree);

    $build['#theme'] = 'menu__thirdlevel';

    return [
      '#theme' => 'mymodule_third_level_menu',
      '#title' => $title,
      '#menu' => $build,
    ];
  }

}
