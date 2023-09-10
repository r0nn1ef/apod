<?php

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Datetime\DrupalDateTime;

define('APOD_TOKEN_TYPE', 'apod');

/**
 * Implements hook_token_info().
 */
function apod_token_info() {
  $tokens = [];

  $tokens['apod']['title'] = [
    'name' => t("Title"),
    'description' => t('The photo/video title.'),
  ];
  $tokens['apod']['explanation'] = [
    'name' => t('Explanation'),
    'description' => t('The image or video details.')
  ];
  $tokens['apod']['url'] = [
    'name' => t('Url'),
    'description' => t('The Url on NASA\'s site for the image.')
  ];
  $tokens['apod']['image'] = [
    'name' => t('Image'),
    'description' => t('The image URL or thumbnail image url if media type is video.')
  ];
  $tokens['apod']['media-type'] = [
    'name' => t('Media type'),
    'description' => t('Media type; either image or video.')
  ];
  $tokens['apod']['video'] = [
    'name' => t('Video'),
    'description' => t('The video URL if media type is video.')
  ];

  return $tokens;
}

/**
 * Implements hook_tokens().
 */
function apod_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  $routeMatch = \Drupal::routeMatch();
  if($type == 'apod' && $routeMatch->getRouteName() == 'apod.date_page') {
    $api = \Drupal::service('apod.api');
    $api_date = DrupalDateTime::createFromTimestamp(strtotime($routeMatch->getRawParameters()->get('date')));

    try {
      $api_data = $api->getImage($api_date, TRUE);
    } catch ( \Exception $e ) {
      \Drupal::logger('apod')->error($e->getMessage());
      $api_data = FALSE;
    }

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'title':
          $replacements[$original] = $api_data->title;
          break;
        case 'explanation':
          $replacements[$original] = $api_data->explanation;
          break;
        case 'url':
          $replacements[$original] = 'https://apod.nasa.gov/apod/ap' . $api_date->format('ymd') . '.html';
          break;
        case 'image':
          $replacements[$original] = $api_data->media_type == 'image' ? $api_data->url : $api_data->thumbnail_url;
          break;
        case 'image_hd':
          $replacements[$original] = $api_data->hdurl ?? '';
          break;
        case 'video':
          $replacements[$original] = $api_data->media_type == 'video' ? $api_data->url : NULL;
          break;
        case 'media-type':
          $replacements[$original] = $api_data->media_type;
      }
    }
  }

  return $replacements;
}