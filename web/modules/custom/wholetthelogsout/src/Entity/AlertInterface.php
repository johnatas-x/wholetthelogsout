<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Alert entities.
 */
interface AlertInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Set the alert as enabled.
   *
   * @return $this
   */
  public function setEnabled(): static;

  /**
   * Set the alert as disabled.
   *
   * @return $this
   */
  public function setDisabled(): static;

  /**
   * Returns whether the alert is enabled.
   *
   * @return bool
   *   TRUE if the alert is enabled, otherwise FALSE.
   */
  public function isEnabled(): bool;

  /**
   * Get the type.
   *
   * @return string|null
   *   The alert type, or NULL, if no value is set.
   */
  public function getType(): ?string;

  /**
   * Get the settings.
   *
   * @return array
   *   The alert settings.
   */
  public function getSettings(): array;

  /**
   * Set the settings.
   *
   * @param array $settings
   *   An array of settings.
   *
   * @return \Drupal\wholetthelogsout\Entity\AlertInterface
   *   The called alert.
   */
  public function setSettings(array $settings): self;

  /**
   * Get the event types.
   *
   * @return array
   *   An array of event types.
   */
  public function getEventTypes(): array;

}
