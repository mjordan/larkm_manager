<?php

namespace Drupal\larkm_manager\Larkm;

/**
 * Utilities for interacting with a Riprap fixity microservice.
 */
class LarkmUtils {

  /**
   * Constructor.
   */
  public function __construct() {
    $config = \Drupal::config('larkm_manager.settings');
    $this->config = $config;
    $this->larkm_base_url = $config->get('larkm_base_url') ?: 'http://localhost:8000/larkm';
  }

  /**
   * Queries a remote copy of Riprap using its REST interface for fixity events.
   *
   * @param string $query
   *   The query to send to larkm.
   *
   * @return string|bool
   *   The raw JSON response body, or false.
   */
  public function searchLarkm(string $query) {
    try {
      $client = \Drupal::httpClient();
      $options = [
        'http_errors' => FALSE,
        'query' => ['q' => $query],
      ];
      $response = $client->request('GET', $this->larkm_base_url . '/search', $options);
      $code = $response->getStatusCode();
      if ($code == 200) {
        $body = $response->getBody()->getContents();
        return $body;
      }
    }
    catch (RequestException $e) {
      \Drupal::logger('larkm_manager')->error($e->getMessage());
      \Drupal::messenger()->addMessage($this->t('Sorry, there has been an error connecting to Larkm, please refer to the system log.'), 'error');
      return FALSE;
    }
  }

  /**
   * Queries larkm for its configuration info.
   *
   * @return array|bool
   *   The 'config' info from larkm, or false.
   */
  public function getConfig() {
    try {
      $client = \Drupal::httpClient();
      $options = [
        'http_errors' => FALSE
      ];
      $response = $client->request('GET', $this->larkm_base_url . '/config', $options);
      $code = $response->getStatusCode();
      if ($code == 200) {
        $body = json_decode($response->getBody()->getContents(), TRUE);
	return $body;
      }
    }
    catch (RequestException $e) {
      \Drupal::logger('larkm_manager')->error($e->getMessage());
      \Drupal::messenger()->addMessage($this->t('Sorry, there has been an error connecting to Larkm, please refer to the system log.'), 'error');
      return FALSE;
    }
  }

  /**
   * Gets the global and local resolver hosts.
   *
   * @return array|bool
   *   Associative array with keys 'global' and 'local', or false.
   */
  public function getResolverHosts() {
    $larkm_config = $this->getConfig();
    $hosts = $larkm_config['resolver_hosts'];
    return $hosts;
  }

  /**
   * Queries larkm for the specified ARK.
   *
   * @return str|bool
   *   A JSON string for the ARK from larkm's search endpoint, or false.
   */
  public function getArk($id) {
    try {
      $client = \Drupal::httpClient();
      $options = [
        'http_errors' => FALSE,
        'query' => ['q' => 'identifier:' . $id],
      ];
      $response = $client->request('GET', $this->larkm_base_url . '/search', $options);
      $code = $response->getStatusCode();
      if ($code == 200) {
        $body = json_decode($response->getBody()->getContents(), TRUE);
	return $body;
      }
    }
    catch (RequestException $e) {
      \Drupal::logger('larkm_manager')->error($e->getMessage());
      \Drupal::messenger()->addMessage($this->t('Sorry, there has been an error connecting to Larkm, please refer to the system log.'), 'error');
      return FALSE;
    }
  }

  /**
   * Adds an ARK to larkm.
   *
   * @return str|bool
   *   HTTP response body if successful, FALSE if not.
   */
  public function addArk($form_state) {
    // Build the JSON to POST.
    $form_values = $form_state->cleanValues()->getValues();

    $ark = [];
    $ark['target'] = $form_values['larkm_add_target'];

    if (strlen($form_values['larkm_add_identifier']) > 0) {
      $ark['identifier'] = $form_values['larkm_add_identifier'];
    }
    if (strlen($form_values['larkm_add_shoulder']) > 0) {
      $ark['shoulder'] = $form_values['larkm_add_shoulder'];
    }
    if (strlen($form_values['larkm_add_erc_what']) > 0) {
      $ark['what'] = $form_values['larkm_add_erc_what'];
    }
    if (strlen($form_values['larkm_add_erc_who']) > 0) {
      $ark['who'] = $form_values['larkm_add_erc_who'];
    }
    if (strlen($form_values['larkm_add_erc_when']) > 0) {
      $ark['when'] = $form_values['larkm_add_erc_when'];
    }
    if (strlen($form_values['larkm_add_erc_where']) > 0) {
      $ark['where'] = $form_values['larkm_add_erc_where'];
    }
    if (strlen($form_values['larkm_add_policy']) > 0) {
      $ark['policy'] = $form_values['larkm_add_policy'];
    }

    $ark_json = json_encode($ark, JSON_UNESCAPED_SLASHES);

    try {
      $client = \Drupal::httpClient();
      $options = [
        'http_errors' => FALSE,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => $ark_json
      ];
      $response = $client->request('POST', $this->larkm_base_url, $options);
      $code = $response->getStatusCode();
      if ($code == 201) {
        $body = json_decode($response->getBody()->getContents(), TRUE);
	return $body;
      }
    }
    catch (RequestException $e) {
      \Drupal::logger('larkm_manager')->error($e->getMessage());
      \Drupal::messenger()->addMessage($this->t('ARK not created. HTTP response code was ' . $reponse_code), 'error');
      return FALSE;
    }
  }

}
