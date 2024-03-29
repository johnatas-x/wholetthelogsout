<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Website entities.
 */
interface WebsiteInterface extends ContentEntityInterface, EntityOwnerInterface {

}
