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
  const ONE_DAY = 86400;

  function __construct() {
    $config = \Drupal::config('apod.settings');
    $this->api_key = $config->get('api_key');
  }

    /**
     * @param DrupalDateTime|NULL $date
     * @param boolean $useHD defaults to FALSE
     * @return false|mixed
     */
  public function getImage(DrupalDateTime $date = NULL, $useHD = FALSE) {
      // This is the first day where an image/video is available.
      $max_date = DrupalDateTime::createFromArray(['year' => 1995, 'month' => 6, 'day' => 16]);
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

    $cid = 'apod:' . $date->format('Y-m-d') . (!$useHD ? '' : '-HD');
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