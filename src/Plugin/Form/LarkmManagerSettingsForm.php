<?php

namespace Drupal\larkm_manager\Plugin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Admin settings form.
 */
class LarkmManagerSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'larkm_manager_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'larkm_manager.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('larkm_manager.settings');
    $form['larkm_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('larkm base URL'),
      '#default_value' => $config->get('larkm_base_url'),
      '#description' => $this->t('Absolute URL to your larkm server.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('larkm_manager.settings')
      ->set('larkm_base_url', $form_state->getValue('larkm_base_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
