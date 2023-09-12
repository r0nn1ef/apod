<?php

/**
 * @file
 * Contains \Drupal\apod\Plugin\Block\APODDefaultBlock.
 */

namespace Drupal\apod\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\apod\Service\APODService;
use Drupal\social_link_field\Plugin\SocialLinkField\Platform\Drupal;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'APODDefaultBlock' block.
 *
 * @Block(
 *  id = "apoddefault_block",
 *  admin_label = @Translation("Astronomy Picture of the Day Block"),
 * )
 */
class APODDefaultBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\apod\Service\APODService $api
   */
  protected $api;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\apod\Service\APODService $apodService
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, APODService $apodService) {
    $this->api = $apodService;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('apod.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'use_random_image' => '',
      'image_date' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['image_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Image date'),
      '#description' => $this->t('The date for the image to display. If no date is entered, the current date will be used.'),
      '#default_value' => $this->configuration['image_date'],
      '#attributes' => [
        'placeholder' => $this->t('Optional'),
      ],
    ];

    $form['use_random_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Random image'),
      '#description' => $this->t('If checked, a random image will be displayed.'),
      '#default_value' => $this->configuration['use_random_image'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // If the count is set, we can only use that field in the api call.
    if (!empty($values['use_random_image'])) {
      // start and end dates can't be used with the count so empty them.
      $form_state->setValue('image_date', '');
    }
    else {
      $max_date = DrupalDateTime::createFromTimestamp(APODService::FIRST_IMAGE_DATE);
      // @todo Need to check dates to see if they are past the first date of the service.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['use_random_image'] = trim($values['use_random_image']);
    $this->configuration['image_date'] = trim($values['image_date']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $count = NULL;
    $image_date = new DrupalDateTime('now');

    if (!empty($this->configuration['use_random_image'])) {
      $count = 100;
      $image_date = NULL;
    }
    else {
      if (!empty($this->configuration['image_date'])) {
        $image_date = DrupalDateTime::createFromTimestamp(strtotime($this->configuration['image_date']));
      }
    }
    $data = $this->api->getImage($image_date, TRUE, $count);
    $image = is_array($data) ? $data[rand(0, count($data) - 1)] : $data;
    $link = Link::createFromRoute(t('Learn more'), 'apod.date_page', ['date' => $image->date]);

    return [
      '#theme' => 'apod_image_block',
      '#data' => [
        'image' => (array)$image,
        'link' => $link,
      ],
      '#attached' => [
        'library' => [
          'apod/default_block',
        ],
      ],
    ];

  }

}
