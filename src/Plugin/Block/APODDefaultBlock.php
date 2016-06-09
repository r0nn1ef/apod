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
    $image = $service->getImage(NULL, TRUE);

    return array(
      '#theme' => ($image->type == 'video' ? 'apod_video' : 'apod_image'),
      '#item' => (array)$image,
      '#attached' => array(
        'library' =>  array(
          'apod/default_block'
        ),
      ),
    );
    
  }

}
