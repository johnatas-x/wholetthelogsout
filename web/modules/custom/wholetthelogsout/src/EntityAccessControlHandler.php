<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Entity\EntityAccessControlHandler as CoreEntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\user\EntityOwnerInterface;

/**
 * Access controller base for custom entities.
 */
abstract class EntityAccessControlHandler extends CoreEntityAccessControlHandler {

  /**
   * Determine if a user has the admin permission of this entity type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check.
   *
   * @return bool
   *   TRUE if the user has the admin permission, otherwiese FALSE.
   */
  public function userHasAdminPermission(AccountInterface $account): bool {
    $permission = $this->getAdminPermission();

    return (is_string($permission) && $account->hasPermission($permission));
  }

  /**
   * Get the admin permission name for this entity type.
   *
   * @return string|bool
   *   The admin permission name.
   */
  public function getAdminPermission(): string|bool {
    return $this->entityType->getAdminPermission();
  }

  /**
   * Helper function to determine if a given user has a give role.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check.
   * @param string $role
   *   The name of the role.
   *
   * @return bool
   *   TRUE if the user has the role, otherwise FALSE.
   */
  public function userHasRole(AccountInterface $account, string $role): bool {
    return $this->userLoad($account)?->hasRole($role) ?: FALSE;
  }

  /**
   * Helper function to load a user entity from an account interface.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to load.
   *
   * @return \Drupal\user\Entity\User|null
   *   The account user entity.
   */
  public function userLoad(AccountInterface $account): ?User {
    return User::load($account->id());
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity,
    $operation,
    AccountInterface $account
  ): AccessResultNeutral|AccessResult|AccessResultAllowed|AccessResultInterface {
    if (!$entity instanceof EntityOwnerInterface) {
      return parent::checkAccess($entity, $operation, $account);
    }

    // Check admin access.
    $admin = $this->userHasAdminPermission($account);

    // Check if the account is the owner of the entity.
    $is_owner = ($entity->getOwnerId() === $account->id());

    // Determine access.
    $access = AccessResult::allowedIf($admin || $is_owner);

    // Add caching.
    $access
      ->cachePerUser()
      ->addCacheableDependency($entity);

    return $access;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account,
    array $context,
    $entity_bundle = NULL
  ): AccessResultNeutral|AccessResult|AccessResultAllowed|AccessResultInterface {
    // Allow authenticated users to create.
    return AccessResult::allowedIf($this->userHasRole($account, 'authenticated'))
      ->addCacheContexts(['user.roles']);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation,
    FieldDefinitionInterface $field_definition,
    AccountInterface $account,
    ?FieldItemListInterface $items = NULL
  ) {
    // Always allow admin access.
    if ($this->userHasAdminPermission($account)) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    // Check if this field is admin-only.
    if ($field_definition->getSetting('admin_only')) {
      // Restrict access to admins.
      return AccessResult::forbidden()->cachePerPermissions();
    }

    return AccessResult::allowed();
  }

}
