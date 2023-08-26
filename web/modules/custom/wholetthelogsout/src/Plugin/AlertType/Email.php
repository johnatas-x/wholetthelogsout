<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Plugin\AlertType;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\wholetthelogsout\Entity\EventInterface;
use Drupal\wholetthelogsout\Entity\WebsiteInterface;
use Drupal\wholetthelogsout\Plugin\AlertTypeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alert type plugin for sending alerts via email.
 *
 * @AlertType(
 *   id = "email",
 *   label = @Translation("Email"),
 * )
 */
class Email extends AlertTypeBase implements ContainerFactoryPluginInterface {

  /**
   * The mail sender service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * {@inheritDoc}
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      MailManagerInterface $mail_manager,
      DateFormatterInterface $date_formatter
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->mailManager = $mail_manager;
    $this->dateFormatter = $date_formatter;
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

    // Build the message parameters.
    $params = [
      'body' => [
        $this->t('The following event has been logged:'),
        '',
        $this->t('Website: @website', ['@website' => $parent->label()]),
        $this->t('Website ID: @uuid', ['@uuid' => $parent->uuid()]),
        $this->t('Event ID: @uuid', ['@uuid' => $event->uuid()]),
        $this->t('Type: @type', ['@type' => $event->getType()]),
        $this->t('Severity: @severity', ['@severity' => $event->getSeverity()]),
        $this->t('User: @user', ['@user' => $event->getUser()]),
        $this->t('URL: @url', ['@url' => ($url = $event->getUrl()) ? $url->toString() : '']),
        $this->t('Date: @date', ['@date' => $this->dateFormatter->format($event->getCreatedTime(), 'long')]),
        $this->t('Expires: @date', ['@date' => $this->dateFormatter->format((int) $event->getExpiration(), 'long')]),
        $this->t('Message: @message', ['@message' => $event->getMessage()]),
        '',
        $this->t('You can view this event here: @link', ['@link' => $event->getUrl()]),
        '',
        $this->t(
            'Please log if you wish to change your alerts: @link',
            ['@link' => Url::fromRoute('<front>')->setAbsolute()->toString()]
        ),
      ],
      'subject' => $this->t('Alert notification for website @website', ['@website' => $parent->label()]),
    ];

    // Send the email.
    $this->mailManager->mail('wholetthelogsout', 'alert', $settings['email'], 'en', $params);

    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultSettings(): array {
    return [
      'email' => '',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function settingsForm(FormStateInterface $form_state): array {
    $settings = $this->getSettings();
    $form = [];
    $form['email'] = [
      '#default_value' => $settings['email'],
      '#description' => t('The email address to send alerts to.'),
      '#required' => TRUE,
      '#title' => t('Email address'),
      '#type' => 'email',
    ];

    return $form;
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
      $container->get('plugin.manager.mail'),
      $container->get('date.formatter')
    );
  }

}
