<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for Event entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class EventHtmlRouteProvider extends EntityHtmlRouteProvider {

  /**
   * {@inheritDoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type): RouteCollection|array {
    $collection = parent::getRoutes($entity_type);

    if ($collection instanceof RouteCollection) {
      // Load the add form route.
      $route = $collection->get('entity.event.add_form');

      // Only allow admins to access since the API will be used to create events.
      $route?->setRequirement('_permission', (string) $entity_type->getAdminPermission());
    }

    return $collection;
  }

}
