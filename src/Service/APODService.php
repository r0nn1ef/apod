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

  function __construct() {
    $config = \Drupal::config('apod.settings');
    $this->api_key = $config->get('api_key');
  }

  public function getImage(\Drupal\Core\Datetime\DrupalDateTime $date = NULL, $useHD = FALSE) {
    if ( is_null($date) ) {
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
          'hd' => (int) $useHD,
        )
      );

      // Create a HTTP client.
      $client = \Drupal::httpClient();

      try {
        $response = $client->get(self::SERVICE_URL, $options);
      } catch ( RequestException $e ) {
        // @todo figure out what to do with the error.
        drupal_set_message($e->getMessage(), 'error');
        return FALSE;
      }

      if ( $response->getStatusCode() == 200 ) {
        $data = json_decode( $response->getBody() );
      } else {
        drupal_set_message('HTTP request resulted in a ' . $response->getStatusCode() . ' response', 'warning');
        return FALSE;
      }

      \Drupal::cache()->set($cid, $data);
    }

    return $data;
  }
}