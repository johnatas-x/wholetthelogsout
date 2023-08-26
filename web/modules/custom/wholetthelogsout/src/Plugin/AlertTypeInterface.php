<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wholetthelogsout\Entity\EventInterface;

/**
 * Defines an interface for Alert type plugins.
 */
interface AlertTypeInterface extends PluginInspectionInterface {

  /**
   * Send an alert.
   *
   * @param \Drupal\wholetthelogsout\Entity\EventInterface $event
   *   The event entity to send an alert for.
   *
   * @return bool
   *   TRUE if the alert was sent, otherwise FALSE.
   */
  public function send(EventInterface $event): bool;

  /**
   * Return the settings for this plugin.
   *
   * @return array
   *   An array of settings.
   */
  public function getSettings(): array;

  /**
   * Provide a configuration form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   A form array.
   */
  public function settingsForm(FormStateInterface $form_state): array;

}
