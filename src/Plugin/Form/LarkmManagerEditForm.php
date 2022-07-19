<?php

namespace Drupal\larkm_manager\Plugin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to edit an ARK in larkm.
 */
class LarkmManagerEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'larkm_manager_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $identifier = \Drupal::request()->query->get('identifier');
    $larkm_utils = \Drupal::service('larkm_manager.larkm_utils');
    $response = $larkm_utils->getArk($identifier);
    $resolver_hosts = $larkm_utils->getResolverHosts();
 
    $form['ark_edit_identifier'] = [
      '#type' => 'item',
      '#title' => t('ARK identifier'),
      '#markup' => $identifier,
    ];
    $form['ark_edit_ark_string'] = [
      '#type' => 'item',
      '#title' => t('ARK string'),
      '#markup' => $response['arks'][0]['ark_string'],
    ];
    $form['ark_edit_global_url'] = [
      '#type' => 'item',
      '#title' => t('Global resolver URL'),
      '#markup' => rtrim($resolver_hosts['global'], '/') . '/' . $response['arks'][0]['ark_string'],
    ];
    $form['ark_edit_local_url'] = [
      '#type' => 'item',
      '#title' => t('Local resolver URL'),
      '#markup' => rtrim($resolver_hosts['local'], '/') . '/' . $response['arks'][0]['ark_string'],
    ];
    $form['larkm_edit_target'] = [
      '#title' => $this->t('Target'),
      '#type' => 'textarea',
      '#maxlength' => 1000,
      '#rows' => 3,
      '#resizable' => TRUE,
      '#default_value' => $response['arks'][0]['target'],
      '#description' => $this->t('The URL this ARK should resolve to. If left empty, will default to value of "Where".'),
    ];
    $form['larkm_edit_erc_what'] = [
      '#title' => $this->t('What'),
      '#type' => 'textarea',
      '#maxlength' => 1000,
      '#rows' => 3,
      '#resizable' => TRUE,
      '#default_value' => $response['arks'][0]['erc_what'],
      '#description' => $this->t('erc_what value.'),
    ];
    $form['larkm_edit_erc_who'] = [
      '#title' => $this->t('Who'),
      '#type' => 'textarea',
      '#maxlength' => 1000,
      '#rows' => 3,
      '#resizable' => TRUE,
      '#default_value' => $response['arks'][0]['erc_who'],
      '#description' => $this->t('erc_who value.'),
    ];
    $form['larkm_edit_erc_when'] = [
      '#title' => $this->t('When'),
      '#type' => 'textarea',
      '#maxlength' => 1000,
      '#rows' => 3,
      '#resizable' => TRUE,
      '#default_value' => $response['arks'][0]['erc_when'],
      '#description' => $this->t('erc_when value.'),
    ];
   $form['larkm_edit_erc_where'] = [
      '#title' => $this->t('Where'),
      '#type' => 'textarea',
      '#maxlength' => 1000,
      '#rows' => 3,
      '#resizable' => TRUE,
      '#default_value' => $response['arks'][0]['erc_where'],
      '#required' => TRUE,
      '#description' => $this->t('erc_where value.'),
    ];
    $form['larkm_edit_policy'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Policy'),
      '#rows' => 5,
      '#maxlength' => 1000,
      '#default_value' => $response['arks'][0]['policy'],
      '#description' => $this->t('The committment policy.'),
    ];
    $form['save_ark'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update ARK'),
    ];    
    $form['larkm_edit_help'] = [
      '#type' => 'item',
      '#markup' => $this->t('Note: If a field other than "Target" is left empty, it will be assigned a default value of ":at".'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . $value);
    }
  }

}
