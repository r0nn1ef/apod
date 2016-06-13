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
use Drupal\Core\Url;
use Drupal\Core\Link;

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

    /*
     * Set up an empty array for our pagination.
     */
    $items = array();

    if ( $date->format('U') > $first_image->format('U') ) {
      $previous_date = DrupalDateTime::createFromTimestamp( $date->format('U') - self::ONE_DAY );
      $items[] = Link::fromTextAndUrl($this->t('&laquo; Previous'), Url::fromRoute('apod.date_page', array('date' => $previous_date->format('Y-m-d'))));
    }

    if ( $date->format('U') < $today->format('U') ) {
      $next_date = DrupalDateTime::createFromTimestamp( $date->format('U') + self::ONE_DAY );
      $items[] = Link::fromTextAndUrl($this->t('Next &raquo;'), Url::fromRoute('apod.date_page', array('date' => $next_date->format('Y-m-d'))));

    }

    // We'll add a quick link to get back to today's image.
    if ( $date->format('U') != $today->format('U')) {
      $items[] = ' ';
      $items[] = Link::fromTextAndUrl($this->t('Today\'s image'), Url::fromRoute('apod.date_page', array('date' => $today->format('Y-m-d'))));
    }

    $build['content'] = array(
      '#theme' => 'apod_content',
      '#image' =>array(
        '#theme' => ($image->type == 'video' ? "apod_video" : "apod_image"),
        '#item' => (array)$image,
        '#attached' => array(
          'library' =>  array(
            'apod/default_page'
          ),
        ),
      ),
      '#image_date' => $date,
      '#links' => array(
        '#theme' => 'item_list',
        '#items' => $items,
        '#list_type' => 'ul',
        '#attributes' => array(
          'id' => 'apod-navigation',
          'class' => array('pager__items')
        ),
      ),
      '#description' => check_markup($image->explanation),
    );

    return $build;

  }
}