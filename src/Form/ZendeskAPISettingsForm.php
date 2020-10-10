<?php

namespace Drupal\webform_zendesk\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the admin settings form.
 */
class ZendeskAPISettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webform_zendesk_settings_form';
  }

  /**
   * Form constructor.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('webform_zendesk_config.settings');

    $form['api_token'] = [
      '#type' => 'textfield',
      '#title' => t('API token'),
      '#description' => t('Your Zendesk API token. See <a href=":url">the Zendesk developer documentation</a> for more details.', [
        ':url' => 'https://developer.zendesk.com/rest_api/docs/support/introduction#api-token',
      ]),
      '#required' => TRUE,
      '#default_value' => $config->get('api_token'),
    ];
    $form['user_name'] = [
      '#type' => 'textfield',
      '#title' => t('Your Zendesk username'),
      '#description' => t('The username of your Zendesk agent. Usernames are e-mails in Zendesk.'),
      '#required' => TRUE,
      '#default_value' => $config->get('user_name'),
    ];
    $form['subdomain'] = [
      '#type' => 'textfield',
      '#title' => t('Zendesk subdomain'),
      '#description' => t("Your subdomain with Zendesk. This is the first part of the url is where you access your Zendesk backend. For example for 'https://example.zendesk.com' the subdomain is 'example'."),
      '#size' => 16,
      '#required' => TRUE,
      '#default_value' => $config->get('subdomain'),
      '#field_prefix' => 'https://',
      '#field_suffix' => '.zendesk.com',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('webform_zendesk_config.settings');

    $config->set('api_token', $form_state->getValue('api_token'));
    $config->set('user_name', $form_state->getValue('user_name'));
    $config->set('subdomain', $form_state->getValue('subdomain'));

    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['webform_zendesk_config.settings'];
  }

}
