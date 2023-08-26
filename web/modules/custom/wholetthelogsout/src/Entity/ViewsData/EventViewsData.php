<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity\ViewsData;

use Drupal\wholetthelogsout\Enum\CustomEntityTypes;

/**
 * Provides Views data for Event entities.
 */
class EventViewsData extends EntityViewsDataBase {

  /**
   * {@inheritDoc}
   */
  public function getEntity(): CustomEntityTypes {
    return CustomEntityTypes::Event;
  }

}
