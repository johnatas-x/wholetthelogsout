<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Services;

use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Helper methods for queries.
 */
class QueryHelper implements QueryHelperInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Creates a new QueryHelper instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function isFiltered(AlterableInterface $query, string $entity_type_id, AccountProxyInterface $user): bool {
    // This is useful for queries done by cron, hooks, etc.
    if ($query->hasTag('access_bypass')) {
      return FALSE;
    }

    if ($query->hasTag('entity_reference')) {
      return TRUE;
    }

    // Get the entity admin permission.
    $perm_def = $this->entityTypeManager->getDefinition($entity_type_id);

    if ($perm_def !== NULL) {
      $permission = $perm_def->getAdminPermission();
    }

    // Check if the user does not have the permission.
    return (isset($permission) && is_string($permission) && !$user->hasPermission($permission));
  }

}
