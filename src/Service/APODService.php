<?php
/**
 * Created by PhpStorm.
 * User: ronald
 * Date: 5/23/16
 * Time: 9:34 AM
 */

namespace Drupal\apod\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;
use GuzzleHttp\Exception\RequestException;

class APODService {

  private $api_key;

  const SERVICE_URL = 'https://api.nasa.gov/planetary/apod';

  /**
   * @var int FIRST_IMAGE_DATE The first date that an astronomy image is availalbe (June 16, 1995).
   */
  const FIRST_IMAGE_DATE = 803278800;
  const ONE_DAY = 86400;

  function __construct() {
    $config = \Drupal::config('apod.settings');
    $this->api_key = $config->get('api_key');
  }

    /**
     * @param DrupalDateTime|NULL $date
     * @param boolean $useHD defaults to FALSE
     * @param int|NULL $count A positive integer, no greater than 100. If this is specified then count randomly chosen images.
     * @param DrupalDateTime|NULL $start_date
     * @param DrupalDateTime|NULL $end_date
     *
     * @return false|mixed
     */
  public function getImage(DrupalDateTime $date = NULL, bool $useHD = FALSE, int $count = NULL, DrupalDateTime $start_date = NULL, DrupalDateTime $end_date = NULL) {
      // This is the first day where an image/video is available.
      $max_date = DrupalDateTime::createFromTimestamp(self::FIRST_IMAGE_DATE);
    /*
     * We want our datetime to be midnight on the given day so we can expire the cache properly.
     */
    if ( is_null($date) ) {
      $date = new DrupalDateTime('now');
    }

    if ( $date->getTimestamp() < $max_date->getTimestamp() ) {
        \Drupal::service('messenger')->addMessage('NASA\'s API only contains image from June 16, 1995 forward.', 'warning');
        $date = new DrupalDateTime();
    }

    if (!empty($count)) {
      $cid = 'apod:random' . (!$useHD ? '' : '-HD');
    }
    else {
      $cid = 'apod:' . $date->format('Y-m-d') . (!$useHD ? '' : '-HD');
    }

    $data = NULL;
    if ( $cache = \Drupal::cache('data')->get($cid)) {
      $data = $cache->data;
    } else {

      $options = array(
        'query' => array(
          'api_key' => $this->api_key,
          'date' => $date->format('Y-m-d'),
          'hd' => $useHD ? 'True': 'False',
          'thumbs' => 'True',
          'concept_tags' => 'True'
        )
      );

      if ( !is_null($count) && intval($count) > 0 ) {
        // $count can not be used with 'date', 'start_date', or 'end_date' params.
        unset($options['query']['date']);
        if ( intval($count) > 100 ) {
          \Drupal::messenger()->addWarning($this->t('COUNT parameter can not be greater than 100. Automatically reducing to the maximum.'));
          $count = 100;
        }
        $options['query']['count'] = intval($count);
      }

      // $count can not be used with 'date', 'start_date', or 'end_date' params.
      if ( !isset($options['query']['count']) && !empty($start_date) ) {
        unset($options['query']['date']);
        $options['query']['start_date'] = $start_date->format('Y-m-d');
        if ( !is_empty($end_date) ) {
          $options['query']['end_date'] = $end_date->format('Y-m-d');
        }
      }

      // Create a HTTP client.
      $client = \Drupal::httpClient();

      try {
        $response = $client->get(self::SERVICE_URL, $options);
      } catch ( RequestException $e ) {
        \Drupal::logger('apod')->alert($e->getMessage());
        return FALSE;
      }

      if ( $response->getStatusCode() == 200 ) {
        $data = json_decode( $response->getBody() );
        \Drupal::logger('apod')->debug('<pre>@data</pre>', ['@data' => print_r($data, true)]);
      } else {
        $message = 'HTTP request resulted in a @status response; @body';
        $params = array(
          '@status' => $response->getStatusCode(),
          '@body' => $response->getReasonPhrase(),
        );
        \Drupal::logger('apod')->critical($message, $params);
        return FALSE;
      }
      
      $expire = $date->format('U') + self::ONE_DAY; // expire the cache in one day.

      \Drupal::cache()->set($cid, $data, $expire);
    }

    return $data;
  }
}