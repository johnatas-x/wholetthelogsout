<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Services;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\wholetthelogsout\Enum\CustomEntityTypes;

/**
 * Provides an interface for queries helper methods.
 */
interface QueryHelperInterface {

  /**
   * Check if the query should bypass access.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The current query.
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return bool
   *   False if the query should bypass access.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function isFiltered(SelectInterface $query, string $entity_type_id): bool;

  /**
   * Global function for hook_query_TAG_alter().
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The query.
   * @param \Drupal\wholetthelogsout\Enum\CustomEntityTypes $entity_type
   *   The tag.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function queryTagAlter(SelectInterface $query, CustomEntityTypes $entity_type): void;

}
