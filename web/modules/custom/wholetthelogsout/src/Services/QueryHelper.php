<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Services;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\wholetthelogsout\Enum\CustomEntityTypes;

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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * Creates a new QueryHelper instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritDoc}
   */
  public function isFiltered(SelectInterface $query, string $entity_type_id): bool {
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
    return (isset($permission) && is_string($permission) && !$this->currentUser->hasPermission($permission));
  }

  /**
   * {@inheritDoc}
   */
  public function queryTagAlter(SelectInterface $query, CustomEntityTypes $entity_type): void {
    $entity_type_id = $entity_type->value;

    // Get the filter.
    $filter = $this->isFiltered($query, $entity_type_id);

    // Check if we should filter.
    if (!$filter) {
      return;
    }

    // Get the query tables.
    $tables = $query->getTables();

    // Determine the alias.
    $alias = isset($tables[$entity_type_id])
      ? $tables[$entity_type_id]['alias']
      : $tables['base_table']['alias'];

    // Generate the field to filter on.
    $field = "$alias.user_id";

    // Iterate the existing conditions.
    foreach ($query->conditions() as $condition) {
      // Check if the user filter condition already exists.
      if (is_array($condition) && ($condition['operator'] === '=') &&
        ($condition['field'] === $field) &&
        ($condition['value'] === $this->currentUser->id())) {
        // No need to filter anymore.
        $filter = FALSE;

        break;
      }
    }

    // Filter for entities that this user owns, if needed.
    if (!$filter) {
      return;
    }

    $query->condition("$alias.user_id", (string) $this->currentUser->id());
  }

}
