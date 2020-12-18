<?php

namespace Drupal\webform_zendesk;

use Drupal\Core\Config\ConfigFactoryInterface;
use Zendesk\API\HttpClient as ZendeskAPI;
use Zendesk\API\Exceptions\ApiResponseException;

/**
 * Zendesk API Helper class.
 */
class ZendeskAPIHelper {

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constuctor function.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Api Auth function.
   */
  public function apiAuth() {
    $config = $this->configFactory->get('webform_zendesk_config.settings');
    $token = $config->get('api_token');
    $subdomain = $config->get('subdomain');
    $username = $config->get('user_name');

    $client = new ZendeskAPI($subdomain);
    try {
      $client->setAuth('basic', ['username' => $username, 'token' => $token]);
      return $client;
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addMessage(t('There was an issue connecting to zendesk API.'), 'error');
      $response = $e->getResponse();
      \Drupal::logger('webform_zendesk')->alert($response->getBody()->getContents());
    }
  }

  /**
   * Create ticket.
   */
  public function createTicket($data, $custom_fields_data = []) {
    $client = $this->apiAuth();
    // Get attachment uploads.
    $upload_token = [];
    if (!empty($data['attachments'])) {
      foreach ($data['attachments'] as $attachment) {
        $attachment_upload = $this->uploadAttachments($attachment);
        $upload_token[] = $attachment_upload->upload->token;
      }
    }
    // Create ticket.
    try {
      $client->tickets()->create([
        'subject' => $data['subject'],
        'comment' => [
          'body' => $data['body'],
          'uploads' => $upload_token,
        ],
        'custom_fields' => $custom_fields_data,
        'priority' => 'normal',
        'requester' => [
          'name' => $data['requester_name'],
          'email' => $data['requester_email'],
        ],
      ]);
    }
    catch (ApiResponseException $e) {
      \Drupal::messenger()->addMessage($e->getMessage(), 'error');
      \Drupal::logger('webform_zendesk')->alert($e->getMessage());
    }
  }

  /**
   * Upload attachments.
   */
  public function uploadAttachments($data) {
    $client = $this->apiAuth();
    try {
      $attachment = $client->attachments()->upload([
        'file' => $data['filepath'],
        'type' => $data['filemime'],
        'name' => $data['filename'],
      ]);
      return $attachment;
    }
    catch (ApiResponseException $e) {
      \Drupal::messenger()->addMessage($e->getMessage(), 'error');
      \Drupal::logger('webform_zendesk')->alert($e->getMessage());
    }
  }

}
