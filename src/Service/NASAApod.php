<?php
/**
 * Created by PhpStorm.
 * User: ronald
 * Date: 5/23/16
 * Time: 9:34 AM
 */

namespace Drupal\apod\Service;


class NASAApod {

  private $api_key;

  function __construct() {
    $this->api_key = \Drupal::config('apod.api_key');
  }
}