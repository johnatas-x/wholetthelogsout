<?php

namespace Drupal\wholetthelogsout\Plugin\Menu\LocalAction;

/**
 * Provides a website contextual link.
 */
class WebsiteEntityContextual extends EntityContextualUuidLocalAction {

  /**
   * {@inheritdoc}
   */
  public function getEntityType(): string {
    return 'website';
  }

}
