<?php

namespace Drupal\larkm_manager\Plugin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to add a new ARK to larkm.
 */
class LarkmManagerAddForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'larkm_manager_add';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['larkm_add_identifier'] = [
      '#title' => $this->t('Identifier'),
      '#type' => 'textfield',
      '#size' => 100,
      '#maxlength' => 200,
      '#description' => $this->t('A v4 UUID. Leave empty to have larkm generate one.'),
    ];

    $larkm_utils = \Drupal::service('larkm_manager.larkm_utils');
    $larkm_config = $larkm_utils->getConfig();
    $shoulders = $larkm_config['allowed_shoulders'];
    // Assumes the default shoulder is first in the list.
    $default_shoulder = array_shift($shoulders);
    $shoulder_options = [];
    $shoulder_options[$default_shoulder] = $default_shoulder . ' (Default)';
    foreach ($shoulders as $shoulder) {
      $shoulder_options[$shoulder] = $shoulder;
    }
    $form['larkm_add_shoulder'] = [
      '#title' => $this->t('Shoulder'),
      '#type' => 'select',
      '#options' => $shoulder_options,
      '#description' => $this->t('The ARK shoulder.'),
    ];

    $form['larkm_add_target'] = [
      '#title' => $this->t('Target'),
      '#type' => 'textfield',
      '#size' => 100,
      '#maxlength' => 200,
      '#required' => TRUE,
      '#description' => $this->t('The URL this ARK should resolve to.'),
    ];
    $form['larkm_add_erc_what'] = [
      '#title' => $this->t('What'),
      '#type' => 'textfield',
      '#size' => 100,
      '#maxlength' => 200,
      '#description' => $this->t('erc_what value.'),
    ];
    $form['larkm_add_erc_who'] = [
      '#title' => $this->t('Who'),
      '#type' => 'textfield',
      '#size' => 100,
      '#maxlength' => 200,
      '#description' => $this->t('erc_who value.'),
    ];
    $form['larkm_add_erc_when'] = [
      '#title' => $this->t('When'),
      '#type' => 'textfield',
      '#size' => 100,
      '#maxlength' => 200,
      '#description' => $this->t('erc_when value.'),
    ];
    $form['larkm_add_erc_where'] = [
      '#title' => $this->t('Where'),
      '#type' => 'textfield',
      '#size' => 100,
      '#maxlength' => 200,
      '#description' => $this->t('erc_where value. If left empty, will default to value of "Target".'),
    ];
    $form['larkm_add_policy'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Policy'),
      '#rows' => 5,
      '#cols' => 100,
      '#maxlength' => 500,
      '#description' => $this->t('The committment policy.'),
    ];
    $form['larkm_add_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add ARK'),
      '#description' => $this->t('Note: If a field is left empty, it will be assigned a default value.'),
    ];
    $form['larkm_add_help'] = [
      '#type' => 'item',
      '#markup' => $this->t('Note: If a field other than "Target" is left empty, it will be assigned a default value.'),
    ];    

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $larkm_utils = \Drupal::service('larkm_manager.larkm_utils');
    $response = $larkm_utils->addArk($form_state);
    if ($response) {
      $message = 'ARK created for "' . $form_state->getValue('larkm_add_target') . '" with global URL "' . $response['urls']['global'] . '", local URL "' . $response['urls']['local'] . '".';
      \Drupal::logger('larkm_manager')->notice($message);
      \Drupal::messenger()->addMessage($message, 'status');
    }
    else {
      $message = 'ARK not created. See Drupal log for more information.';
      \Drupal::messenger()->addMessage($message, 'error');
    }
  }

}
