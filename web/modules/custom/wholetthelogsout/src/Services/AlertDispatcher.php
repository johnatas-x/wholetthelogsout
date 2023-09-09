<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\wholetthelogsout\Entity\AlertInterface;
use Drupal\wholetthelogsout\Entity\EventInterface;
use Drupal\wholetthelogsout\Entity\Website;
use Drupal\wholetthelogsout\Plugin\AlertTypeInterface;
use Drupal\wholetthelogsout\Plugin\AlertTypeManager;

/**
 * Dispatch alerts for events.
 */
class AlertDispatcher implements AlertDispatcherInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The alert type plugin manager.
   *
   * @var \Drupal\wholetthelogsout\Plugin\AlertTypeManager
   */
  protected AlertTypeManager $alertTypeManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected PathMatcherInterface $pathMatcher;

  /**
   * Constructs a new AlertDispatcher object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\wholetthelogsout\Plugin\AlertTypeManager $alert_type_manager
   *   The alert type plugin manager.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    AlertTypeManager $alert_type_manager,
    PathMatcherInterface $path_matcher
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->alertTypeManager = $alert_type_manager;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * {@inheritDoc}
   */
  public function dispatch(EventInterface $event): int {
    $alerts = $this->loadEventAlerts($event);

    // Load matching alerts.
    if (!empty($alerts)) {
      // Iterate the alerts.
      foreach ($alerts as $alert) {
        // Dispatch the alert.
        $instance = $this->alertTypeManager->createInstanceFromAlert($alert);
        assert($instance instanceof AlertTypeInterface);
        $instance->send($event);
      }

      return count($alerts);
    }

    return 0;
  }

  /**
   * {@inheritDoc}
   */
  public function loadEventAlerts(EventInterface $event): array {
    // Load alert storage.
    $storage = $this->entityTypeManager
      ->getStorage('alert');

    $parent = $event->getParent();

    if (!$parent instanceof Website) {
      return [];
    }

    // Query to find the matching alerts.
    $ids = $storage
      ->getQuery()
      ->accessCheck()
      ->condition('enabled', 1)
      ->condition('website', $parent->id())
      ->condition('event_severity', $event->getSeverity())
      ->execute();

    // Load the alerts.
    $alerts = !empty($ids)
        ? $storage->loadMultiple($ids)
        : [];

    // Iterate the alerts.
    foreach ($alerts as $index => $alert) {
      assert($alert instanceof AlertInterface);
      $types = $alert->getEventTypes();

      // Check if this alert has event type filters and if the type is not a match.
      if (empty($types) || $this->pathMatcher->matchPath((string) $event->getType(), implode("\n", $types))) {
        continue;
      }

      // Remove this alert.
      unset($alerts[$index]);
    }

    return $alerts;
  }

}
