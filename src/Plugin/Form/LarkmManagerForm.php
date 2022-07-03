<?php

namespace Drupal\larkm_manager\Plugin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Url;
use \Drupal\Core\Link;

/**
 * Admin settings form.
 */
class LarkmManagerForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'larkm_manager_home';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $add_ark_url = Url::fromRoute('larkm_manager.add_ark');
    $add_ark_link = new Link(t('Add ARK'), $add_ark_url);
    $form['add_ark'] = [
      '#type' => 'item',
      '#markup' => $add_ark_link->toString(),
    ];    
    $form['larkm_query'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Search for ARKs.'),
    ];
    $form['search_arks'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search ARKs'),
    ];    
    if (NULL != $form_state->get('search_arks_results')) {
      $form['search_arks_results'] = [
        '#type' => 'table',
        '#header' => $form_state->get(['search_arks_results','header']),
        '#rows' => $form_state->get(['search_arks_results', 'rows']),
      ];
    } 

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // foreach ($form_state->getValues() as $key => $value) {
      // \Drupal::messenger()->addMessage($key . ': ' . $value);
    // }

    $larkm_utils = \Drupal::service('larkm_manager.larkm_utils');
    $arks = $larkm_utils->searchLarkm($form_state->getValue('larkm_query')); 
    $arks = json_decode($arks, TRUE);
    $rows = [];
    foreach ($arks['arks'] as $ark) {
      $target_url = Url::fromUri($ark['target']);
      $target_link = new Link($target_url->toString(), $target_url);
      $edit_url = Url::fromRoute('larkm_manager.edit_ark', ['identifier' => $ark['identifier']]);
      $edit_link = new Link(t('Edit'), $edit_url);
      $rows[] = [$ark['erc_what'], $ark['ark_string'], $target_link, $edit_link];
    }
  
    $header = [
      'erc_what' => t('erc_what'),
      'ark_string' => t('ark_string'),
      'target' => t('target'),
      'operations' => t('operations'),
    ];
    $form_state->set(['search_arks_results','header'], $header);
    $form_state->set(['search_arks_results', 'rows'], $rows);
    $form_state->setRebuild(TRUE);
  }

}
