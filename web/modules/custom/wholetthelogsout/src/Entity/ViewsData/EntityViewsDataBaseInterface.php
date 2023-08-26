<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity\ViewsData;

use Drupal\wholetthelogsout\Enum\CustomEntityTypes;

/**
 * Provides an interface for defining all custom entities views data.
 */
interface EntityViewsDataBaseInterface {

  /**
   * Return the entity type enum case.
   *
   * @return \Drupal\wholetthelogsout\Enum\CustomEntityTypes
   *   Entity type enum.
   */
  public function getEntity(): CustomEntityTypes;

}
