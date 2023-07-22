<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Services;

use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Provides an interface for queries helper methods.
 */
interface QueryHelperInterface {

  /**
   * Check if the query should bypass access.
   *
   * @param \Drupal\Core\Database\Query\AlterableInterface $query
   *   The current query.
   * @param string $entity_type_id
   *   The entity type ID.
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The current user.
   *
   * @return bool
   *   False if the query should bypass access.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function isFiltered(AlterableInterface $query, string $entity_type_id, AccountProxyInterface $user): bool;

}
