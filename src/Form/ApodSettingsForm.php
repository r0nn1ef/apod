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

class ApodSettingsForm extends ConfigFormBase
{

    public function getFormId()
    {
        return 'apod_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('apod.settings');

        $url = Url::fromUri('https://api.nasa.gov/index.html', array('fragment' => 'apply-for-an-api-key',))->toUriString();

        $form['api_key'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('API Key'),
            '#description' => $this->t('<a href=":link" target="_blank">Register for a NASA API key.</a>', array(':link' => $url)),
            '#default_value' => $config->get('api_key'),
        );

        $markup = <<<MARKUP
<h4>Web Service Rate Limits</h4>
                  <p>Limits are placed on the number of API requests you may make
                      using your API key. Rate limits may vary by service, but the
                      defaults are:
                  </p>
                  <ul>
                      <li>Hourly Limit: 1,000 requests per hour</li>
                  </ul>
                  <p>For each API key, these limits are applied across all
                      api.nasa.gov API requests. Exceeding these limits will lead to your
                      API key being temporarily blocked from making further requests. The
                      block will automatically be lifted by waiting an hour. If you need
                      higher rate limits, contact us.
                  </p>
                  <h3>DEMO_KEY Rate Limits</h3>
                  <p>In documentation examples, the special DEMO_KEY api key is used.
                      This API key can be used for initially exploring APIs prior to
                      signing up, but it has much lower rate limits, so you’re
                      encouraged to signup for your own API key if you plan to use the API
                      (signup is quick and easy). The rate limits for the DEMO_KEY
                      are:
                  </p>
                  <ul>
                      <li>Hourly Limit: 30 requests per IP address per hour</li>
                      <li>Daily Limit: 50 requests per IP address per day</li>
                  </ul>
                  <h4>How Do I See My Current Usage?</h4>
                  <p>Your can check your current rate limit and usage details by
                      inspecting the <code>X-RateLimit-Limit</code>
                      and <code>X-RateLimit-Remaining</code> HTTP
                      headers that are returned on every API response. For example, if an
                      API has the default hourly limit of 1,000 request, after making 2
                      requests, you will receive this HTTP header in the response of the
                      second request:
                  </p>
                  <p><code>X-RateLimit-Remaining: 998</code></p>
MARKUP;


        $form['rate_limit_description'] = [
            '#type' => 'item',
            '#markup' => $markup,
        ];

        return parent::buildForm($form, $form_state);
    }

    public function getEditableConfigNames()
    {
        return ["apod.settings"];
    }

    public function getConfig()
    {
        return parent::getConfig();
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $config = $this->config('apod.settings');
        $value = $form_state->getValue('api_key');
        if (empty($value)) {
            $value = 'DEMO_KEY';
        }

        if ($config->get('api_key') != $value) {
            $message = 'NASA API key changed by @user from @old to @new on @date.';
            $params = array(
                '@user' => \Drupal::currentUser()->getAccountName(),
                '@old' => $config->get('api_key'),
                '@new' => $value,
                '@date' => \Drupal::service('date.formatter')->format(\Drupal::time()->getRequestTime(), 'custom', 'Y-m-j H:i:s A'),
            );
            \Drupal::logger('apod')->warning($message, $params);
        }

        $config->set('api_key', $value);
        $config->save();
        parent::submitForm($form, $form_state);
    }
}