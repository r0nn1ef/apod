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

  const ONE_DAY = 86400;

  public function content($date=NULL) {
    // First Astronomy Picture of the day appears to be July 1, 1995.
    $first_image = DrupalDateTime::createFromTimestamp( mktime(0, 0, 0, 7, 1, 1995) );
    $today = DrupalDateTime::createFromTimestamp( mktime(0, 0, 0, date('m'), date('j'), date('Y')) );

    if ( !empty($date) ) {
      if ( is_int($date) ) {
        $date = DrupalDateTime::createFromTimestamp($date);
      } elseif ( is_string( $date ) && preg_match( '/[0-9]{4}(\-[0-9]{2}){2}/', $date ) ) {
        $date = DrupalDateTime::createFromTimestamp( strtotime( $date ) );
      } else {
        $date = NULL;
      }
    } else {
      $date = $today;
    }

    $service = \Drupal::service('apod.service');

    $image = $service->getImage($date, TRUE);

    $build = array();
    


    $build['image'] = array(
      '#type' => 'markup',
      '#markup' => '<pre>' . print_r($image, TRUE) . '</pre>',
    );

    if ( $date->format('U') > $first_image->format('U') ) {
      $previous_date = DrupalDateTime::createFromTimestamp( $date->format('U') - self::ONE_DAY );
      $path = 'astronomy-picture-of-the-day/' . $previous_date->format('Y-m-d');
      $build['prev_link'] = array(
        '#theme' => 'link',
        '#path' => $path,
        '#alt' => '',
        '#text' => $this->t('&laquo; Previous')
      );
    }

    if ( $date->format('U') > $today->format('U') ) {
      $next_date = DrupalDateTime::createFromTimestamp( $date->format('U') + self::ONE_DAY );
      $path = 'astronomy-picture-of-the-day/' . $next_date->format('Y-m-d');
      $build['next_link'] = array(
        '#theme' => 'link',
        '#path' => $path,
        '#alt' => '',
        '#text' => $this->t('Next &raquo;')
      );
    }

    return $build;
  }
}