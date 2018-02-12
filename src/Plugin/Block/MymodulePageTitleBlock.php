<?php

namespace Drupal\Mymodule\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a Mymodule Page Title block.
 *
 * @Block(
 *   id = "Mymodule_page_title_block",
 *   admin_label = @Translation("Configurable Page Title block"),
 *   category = "core"
 * )
 */
class MymodulePageTitleBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $title = $config['label'];
    return [
      '#theme' => 'page_title',
      '#title' => $title,
    ];
  }

}
