<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Plugin\AlertType;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\wholetthelogsout\Entity\EventInterface;
use Drupal\wholetthelogsout\Entity\WebsiteInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alert type plugin for sending alerts via Slack.
 *
 * @AlertType(
 *   id = "slack",
 *   label = @Translation("Slack"),
 * )
 */
class Slack extends Webhook {

  /**
   * Maximum event message length.
   */
  private const MESSAGE_LENGTH = 500;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * {@inheritDoc}
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      ClientInterface $http_client,
      ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $http_client);

    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function send(EventInterface $event): bool {
    if (!$event->getParent() instanceof WebsiteInterface) {
      return FALSE;
    }

    $settings = $this->getSettings();

    // Extract the message.
    $message = (string) $event->getMessage();

    // Check if the message needs to be trimmed.
    if (strlen($message) > self::MESSAGE_LENGTH) {
      // Trim the message.
      $message = substr($message, 0, self::MESSAGE_LENGTH) . '...';
    }

    // Queue the request.
    $this->queueHttpRequest('POST', $settings['endpoint'], [
      'body' => Json::encode($this->getData($event, $settings)),
    ]);

    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultSettings(): array {
    return [
      'channel' => '',
      'endpoint' => '',
      'username' => $this->configFactory->get('system.site')->get('name'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function settingsForm(FormStateInterface $form_state): array {
    $settings = $this->getSettings();
    $form = [];
    $form['endpoint'] = [
      '#default_value' => $settings['endpoint'],
      '#description' => t('The Slack webhook URL. This must begin with https://hooks.slack.com.'),
      '#required' => TRUE,
      '#title' => t('Webhook URL'),
      '#type' => 'url',
    ];
    $form['channel'] = [
      '#default_value' => $settings['channel'],
      '#description' => t(
          'The Slack channel to post the message in. This must start with # for a room or @ for a direct message.'
      ),
      '#required' => TRUE,
      '#title' => t('Channel'),
      '#type' => 'textfield',
    ];
    $form['username'] = [
      '#default_value' => $settings['username'],
      '#description' => t(
          'The username to post the message as. This name does not have to exist in your Slack organization.'
      ),
      '#required' => TRUE,
      '#title' => t('Username'),
      '#type' => 'textfield',
    ];
    $form['setup'] = $this->getSetupSettings();

    return $form;
  }

  /**
   * Validate the configuration form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function validateSettingsForm(FormStateInterface $form_state): void {
    // Extract the settings.
    $settings = (array) $form_state->getValue('settings_form');

    // Make sure the channel starts with a # or @.
    $channel = !empty($settings['channel']) && is_string($settings['channel'])
      ? $settings['channel']
      : '';

    if (!in_array(substr($channel, 0, 1), ['#', '@'], TRUE)) {
      $form_state->setErrorByName(
        'settings_form][channel',
        $this->t('The Slack channel must start with a # or @.')->render()
      );
    }

    // Check the webhook URL.
    $endpoint = !empty($settings['endpoint']) && is_string($settings['endpoint'])
      ? $settings['endpoint']
      : '';

    if (str_starts_with($endpoint, 'https://hooks.slack.com')) {
      return;
    }

    $form_state->setErrorByName(
      'settings_form][endpoint',
      $this->t('The Slack webhook URL must begin with https://hooks.slack.com.')->render()
    );
  }

  /**
   * {@inheritDoc}
   */
  public static function create(
      ContainerInterface $container,
      array $configuration,
      $plugin_id,
      $plugin_definition
  ): Webhook|ContainerFactoryPluginInterface|static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->configFactory = $container->get('config.factory');

    return $instance;
  }

  /**
   * Slack webhook setup instructions.
   *
   * @return array
   *   Form settings.
   */
  private function getSetupSettings(): array {
    return [
      '#open' => FALSE,
      '#title' => t('Slack webhook setup instructions'),
      '#type' => 'details',
      'list' => [
        '#items' => [
          $this->t('In the left sidebar, click on "Apps". You may need to be an administrator to access this.'),
          $this->t('Search for the "incoming-webhook" application and press "Install".'),
          $this->t('Copy the Webhook URL in to the field here.'),
        ],
        '#theme' => 'item_list',
      ],
    ];
  }

  /**
   * Build the Slack data.
   *
   * @param \Drupal\wholetthelogsout\Entity\EventInterface $event
   *   The event.
   * @param array $settings
   *   The current settings.
   *
   * @return array
   *   The Slack data.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function getData(EventInterface $event, array $settings): array {
    return [
      'attachments' => [
        [
          'author_link' => Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(),
          'author_name' => $this->getDefaultSettings()['username'],
          'fields' => $this->getFieldsData($event),
          'pretext' => $this->t('A notification was dispatched for the following event'),
          'title' => strip_tags((string) $event->label()),
          'title_link' => $event->toUrl('canonical', ['absolute' => TRUE])->toString(),
        ],
      ],
      'channel' => $settings['channel'],
      'username' => $settings['username'],
    ];
  }

  /**
   * Get data for fields.
   *
   * @param \Drupal\wholetthelogsout\Entity\EventInterface $event
   *   The envent.
   *
   * @return array
   *   The data.
   */
  private function getFieldsData(EventInterface $event): array {
    assert($event->getParent() instanceof WebsiteInterface);

    return [
      [
        'short' => TRUE,
        'title' => $this->t('Website'),
        'value' => $event->getParent()->label(),
      ],
      [
        'short' => TRUE,
        'title' => $this->t('Type'),
        'value' => $event->getType(),
      ],
      [
        'short' => TRUE,
        'title' => $this->t('Severity'),
        'value' => $event->getSeverity(),
      ],
      [
        'short' => TRUE,
        'title' => $this->t('User'),
        'value' => $event->getUser(),
      ],
      [
        'short' => FALSE,
        'title' => $this->t('Message'),
        'value' => (string) $event->getMessage(),
      ],
    ];
  }

}
