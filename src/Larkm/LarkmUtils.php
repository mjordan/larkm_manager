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
    }
  }

}
