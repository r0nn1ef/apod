<?php

/**
 * @file
 * Contains \Drupal\apod\Plugin\Block\APODDefaultBlock.
 */

namespace Drupal\apod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;

/**
 * Provides a 'APODDefaultBlock' block.
 *
 * @Block(
 *  id = "apoddefault_block",
 *  admin_label = @Translation("Astronomy Picture of the Day Block"),
 * )
 */
class APODDefaultBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    $service = \Drupal::service('apod.service');
    $build = [];
    $build['apoddefault_block']['#markup'] = '<pre>' . print_r($service->getImage(NULL, TRUE), TRUE) . '</pre>';

    return $build;
  }

}
