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
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApodController extends ControllerBase {

  const ONE_DAY = 86400;
  const APOD_DATE_DEFAULT_FORMAT = 'Y-m-d';

  public function index() {
    $today = new DrupalDateTime('now', date_default_timezone_get() );
    return $this->redirect('apod.date_page', ['date' => $today->format('Y-m-d')]);
  }

  public function content($date=NULL) {
    // First Astronomy Picture of the day appears to be July 1, 1995.
    $first_image = DrupalDateTime::createFromTimestamp( strtotime('1995-06-16') );
    $today = new DrupalDateTime('now');
    $today->setTime(0,0,0);

    if ( !empty($date) ) {
      if ( is_string( $date ) && preg_match( '/[0-9]{4}(\-[0-9]{2}){2}/', $date ) ) {
        $date = DrupalDateTime::createFromTimestamp( strtotime( $date ) );
      } else {
        $date = $today;
      }
    } else {
      $date = $today;
    }

    /*
     * The NASA api doesn't let you get images for dates in the future.
     */
    if ( $date->format('U') > $today->format('U')) {
      throw new NotFoundHttpException();
    }

    $service = \Drupal::service('apod.api');
    $image = $service->getImage($date, TRUE);

    /*
     * Set up an empty array for our pagination.
     */
    $items = array();

    if ( $date->format('U') > $first_image->format('U') ) {
      $previous_date = DrupalDateTime::createFromTimestamp( $date->format('U') - self::ONE_DAY );
      $items[] = Link::fromTextAndUrl($this->t( '&laquo; Previous' ), Url::fromRoute( 'apod.date_page', array( 'date' => $previous_date->format( self::APOD_DATE_DEFAULT_FORMAT ) ) ) );
    }

    if ( $date->format('U') < $today->format('U') ) {
      $next_date = DrupalDateTime::createFromTimestamp( $date->format('U') + self::ONE_DAY );
      $items[] = Link::fromTextAndUrl($this->t( 'Next &raquo;' ), Url::fromRoute( 'apod.date_page', array( 'date' => $next_date->format( self::APOD_DATE_DEFAULT_FORMAT ) ) ) );
    }

    $build['content'] = array(
      '#theme' => 'apod_content',
      '#title' => array('#plain_text' => $image->title),
      '#image' =>array(
        '#theme' => ($image->media_type == 'video' ? "apod_video" : "apod_image"),
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
      '#copyright' => check_markup(($image->copyright ?? ''))
    );

    return $build;

  }
}