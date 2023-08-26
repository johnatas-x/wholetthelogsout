<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for Alert type plugins.
 */
abstract class AlertTypeBase extends PluginBase implements AlertTypeInterface {

  use StringTranslationTrait;

  /**
   * The plugin settings.
   *
   * @var string[]
   */
  protected array $settings;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->settings = array_merge($this->getDefaultSettings(), $configuration);
  }

  /**
   * {@inheritDoc}
   */
  public function getSettings(): array {
    return $this->settings;
  }

  /**
   * {@inheritDoc}
   */
  public function settingsForm(FormStateInterface $form_state): array {
    return [];
  }

  /**
   * Return an array of default settings.
   *
   * @return array
   *   An array of default settings.
   */
  public function getDefaultSettings(): array {
    return [];
  }

}
