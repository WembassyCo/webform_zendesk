<?php

namespace Drupal\webform_zendesk;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Zendesk\API\HttpClient as ZendeskAPI;
use Zendesk\API\Exceptions\ApiResponseException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Zendesk API Helper class.
 */
class ZendeskAPIHelper {

  use StringTranslationTrait;

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructor function.
   */
  public function __construct(ConfigFactoryInterface $configFactory, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger) {
    $this->configFactory = $configFactory;
    $this->messenger = $messenger;
    $this->logger = $logger;
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
      $this->messenger->addMessage($this->t('There was an issue connecting to zendesk API.'), 'error');
      $response = $e->getResponse();
      $this->logger->get('webform_zendesk')->alert($response->getBody()->getContents());
    }
  }

  /**
   * Create / update the users.
   *
   * @param string $email
   *   The email address of the user.
   * @param array $user_data
   *   The user data.
   *
   * @return object
   *   The user object.
   */
  public function createUpdateUser($email, array $user_data = []) {
    $client = $this->apiAuth();
    $user = $this->findUserByEmail($email);
    if (!empty($user)) {
      // Update the existing user.
      $client->users()->update($user->id, $user_data);
    }
    else {
      // Create new user.
      $user = $client->users()->create($user_data);
    }
    return $user;
  }

  /**
   * Find the user by email id.
   *
   * @param string $email
   *   The email id.
   *
   * @return object|null
   *   Return the user object if found, null otherwise.
   */
  public function findUserByEmail($email) {
    $client = $this->apiAuth();
    $result = $client->users()->search(['query' => $email]);

    if ($result->count) {
      return $result->users[0];
    }
    return NULL;
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

    // Get the user.
    $user = $this->createUpdateUser($data['requester_email'], $data['user_data']);

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
          'email' => $user->email,
        ],
      ]);
    }
    catch (ApiResponseException $e) {
      $this->messenger->addMessage($e->getMessage(), 'error');
      $this->logger->get('webform_zendesk')->alert($e->getMessage());
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
      $this->messenger->addMessage($e->getMessage(), 'error');
      $this->logger->get('webform_zendesk')->alert($e->getMessage());
    }
  }

}
