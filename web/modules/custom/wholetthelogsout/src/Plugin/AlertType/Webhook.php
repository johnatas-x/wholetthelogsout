<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Plugin\AlertType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\wholetthelogsout\Entity\EventInterface;
use Drupal\wholetthelogsout\Entity\WebsiteInterface;
use Drupal\wholetthelogsout\Plugin\AlertTypeBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alert type plugin for sending alerts via webhook.
 *
 * @AlertType(
 *   id = "webhook",
 *   label = @Translation("Webhook"),
 * )
 */
class Webhook extends AlertTypeBase implements ContainerFactoryPluginInterface {

  /**
   * The request timeout length, in seconds.
   */
  private const TIMEOUT = 3;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * The requests.
   *
   * @var \GuzzleHttp\Promise\PromiseInterface[]
   */
  protected array $requests = [];

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->httpClient = $http_client;
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function send(EventInterface $event): bool {
    $settings = $this->getSettings();
    $parent = $event->getParent();

    if (!$parent instanceof WebsiteInterface) {
      return FALSE;
    }

    // Build the data to post.
    $data = [
      'created' => $event->getCreatedTime(),
      'event' => $event->uuid(),
      'expire' => $event->getExpiration(),
      'message' => $event->getMessage(),
      'severity' => $event->getSeverity(),
      'type' => $event->getType(),
      'url' => ($url = $event->getUrl()) ? $url->toString() : NULL,
      'user' => $event->getUser(),
      'website' => $parent->uuid(),
      'websiteName' => $parent->label(),
    ];

    // Queue the request.
    $this->queueHttpRequest('POST', $settings['endpoint'], [
      'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ],
      'json' => $data,
    ]);

    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultSettings(): array {
    return [
      'endpoint' => '',
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
      '#description' => t('The endpoint URL to POST the event data to.'),
      '#required' => TRUE,
      '#title' => t('Endpoint'),
      '#type' => 'url',
    ];
    $form['example'] = [
      '#open' => FALSE,
      '#title' => t('Example request'),
      '#type' => 'details',
      'headers' => [
        'code' => [
          '#tag' => 'pre',
          '#type' => 'html_tag',
          '#value' => "Accept: application/json\nContent-Type: application/json",
        ],
        'label' => [
          '#tag' => 'h4',
          '#type' => 'html_tag',
          '#value' => $this->t('Headers'),
        ],
      ],
      'payload' => $this->getPayloadExemple(),
    ];

    return $form;
  }

  /**
   * Queue an HTTP request to be made during shutdown.
   *
   * All requests are queued and executed in a shutdown function asynchronously
   * so that the page response does not have to wait for the requests to finish.
   *
   * @param string $method
   *   The HTTP request method.
   * @param string $uri
   *   The HTTP request endpoint URI.
   * @param array $options
   *   An array of request options.
   */
  public function queueHttpRequest(string $method, string $uri, array $options = []): void {
    // Register a shutdown function if this is the first request to be queued.
    if (empty($this->requests)) {
      drupal_register_shutdown_function([$this, 'executeQueuedHttpRequests']);
    }

    // Merge in option defaults.
    $options = array_merge([
      'allow_redirects' => FALSE,
      'timeout' => self::TIMEOUT,
    ], $options);

    // Create and store the request.
    $this->requests[] = $this->httpClient->requestAsync($method, $uri, $options);
  }

  /**
   * Asynchronously execute all queued HTTP requests.
   *
   * @see queueHttpRequest()
   */
  public function executeQueuedHttpRequests(): void {
    Utils::settle($this->requests)->wait();
  }

  /**
   * {@inheritDoc}
   */
  public static function create(
      ContainerInterface $container,
      array $configuration,
      $plugin_id,
      $plugin_definition
  ): self|ContainerFactoryPluginInterface|static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  /**
   * Returns the payload example.
   *
   * @return array
   *   Payload example.
   */
  private function getPayloadExemple(): array {
    return [
      'code' => [
        '#tag' => 'pre',
        '#type' => 'html_tag',
        '#value' => '{
   "channel":"742caa49-19e9-4126-ad35-4089d3eee13b",
   "channelName":"Ecommerce store",
   "event":"821cda32-32e2-1423-sa32-4723d3asf33p",
   "type":"order",
   "severity":"notice",
   "user":"John Doe",
   "url":"http:\/\/mystore.com/cart",
   "created":1523451294,
   "expire":1524660416,
   "message":"A new order was completed for $100.00"
}',
      ],
      'label' => [
        '#tag' => 'h4',
        '#type' => 'html_tag',
        '#value' => $this->t('Payload'),
      ],
    ];
  }

}
