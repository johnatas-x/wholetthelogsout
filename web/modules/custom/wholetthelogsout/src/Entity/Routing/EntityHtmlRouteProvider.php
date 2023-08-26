<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for custom entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class EntityHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * Available links.
   */
  private const AVAILABLE_LINKS = [
    'canonical',
    'edit_form',
    'delete_form',
  ];

  /**
   * {@inheritDoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type): array|RouteCollection {
    $collection = parent::getRoutes($entity_type);
    $entity_type_id = $entity_type->id();

    // Switch the UUID.
    foreach (self::AVAILABLE_LINKS as $link) {
      $route = $collection->get("entity.$entity_type_id.$link");
      $route?->setOption('parameters', [
        $entity_type_id => [
          'entity_type_id' => $entity_type_id,
          'type' => 'entity_uuid',
        ],
      ]);
      $route?->setRequirement($entity_type_id, '[\w\-]+');
    }

    return $collection;
  }

}
