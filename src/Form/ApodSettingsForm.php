<?php
/**
 * Created by PhpStorm.
 * User: ronald
 * Date: 5/23/16
 * Time: 9:47 AM
 */

namespace Drupal\apod\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

class ApodSettingsForm extends ConfigFormBase {

  public function getFormId() {
    return 'apod_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('apod.settings');
    
    $url = Url::fromUri('https://api.nasa.gov/index.html', array('fragment' => 'apply-for-an-api-key',) )->toUriString();

    $form['api_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('api.nasa.gov key for expanded usage; <a href=":link" target="_blank">Register for a NASA API key.</a>', array(':link' => $url)),
      '#default_value' => $config->get('api_key'),
    );

    return parent::buildForm($form, $form_state);
  }

  public function getEditableConfigNames() {
    return ["apod.settings"];
  }

  public function getConfig() {
    return parent::getConfig();
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('apod.settings');
    $config->set('api_key', $form_state->getValue('api_key') );
    $config->save();
    parent::submitForm($form, $form_state);
  }
}