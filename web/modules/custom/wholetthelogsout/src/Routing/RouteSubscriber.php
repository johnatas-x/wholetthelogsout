<?php

namespace Drupal\wholetthelogsout\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\wholetthelogsout\Enum\CustomEntityTypes;
use Symfony\Component\Routing\RouteCollection;

/**
 * Modify routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * List the endpoints.
   */
  private const ENDPOINTS = [
    'collection',
    'individual',
    'related',
    'relationship',
  ];

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    // Remove CSRF requirement, require auth user role, only allow key
    // authentication for the custom entity type API routes.
    foreach (CustomEntityTypes::cases() as $type) {
      // Iterate the endpoints.
      foreach (self::ENDPOINTS as $endpoint) {
        $route = $collection->get("jsonapi.$type->value.$endpoint");
        // Load the route.
        if ($route) {
          $route->setRequirement('_role', 'authenticated');
          $requirements = $route->getRequirements();
          unset($requirements['_csrf_request_header_token']);
          $route->setRequirements($requirements);
          $route->setOption('_auth', ['key_auth']);
        }
      }
    }
  }

}
