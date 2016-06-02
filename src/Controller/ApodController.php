<?php

/**
 * Created by PhpStorm.
 * User: ronald
 * Date: 6/2/16
 * Time: 3:36 PM
 */
namespace Drupal\apod\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;

class ApodController extends ControllerBase {

  public function content($date=NULL) {

    if ( !empty($date) ) {
      if ( is_int($date) ) {
        $date = DrupalDateTime::createFromTimestamp($date);
      } elseif ( is_string( $date ) && preg_match( '/[0-9]{4}(\-[0-9]{2}){2}/', $date ) ) {
        $date = DrupalDateTime::createFromTimestamp( strtotime( $date ) );
      } else {
        $date = NULL;
      }
    }

    $service = \Drupal::service('apod.service');

    $image = $service->getImage($date, TRUE);



    return array(
      '#type' => 'markup',
      '#markup' => '<pre>' . print_r($image, TRUE) . '</pre>',
    );
  }
}