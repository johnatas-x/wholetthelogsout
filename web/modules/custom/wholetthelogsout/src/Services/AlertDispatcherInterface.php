<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Services;

use Drupal\wholetthelogsout\Entity\EventInterface;

/**
 * Provides an interface for dispatch methods.
 */
interface AlertDispatcherInterface {

  /**
   * Dispatch alerts for a given event.
   *
   * @param \Drupal\wholetthelogsout\Entity\EventInterface $event
   *   An event entity.
   *
   * @return int
   *   The amount of alerts dispatched, 0 if there were none.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function dispatch(EventInterface $event): int;

  /**
   * Load enabled alert entities that match a given event.
   *
   * @param \Drupal\wholetthelogsout\Entity\EventInterface $event
   *   An event entity.
   *
   * @return array
   *   An array of Alert entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadEventAlerts(EventInterface $event): array;

}
