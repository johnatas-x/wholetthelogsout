<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Website entities.
 */
class WebsiteViewsData extends EntityViewsData {

  /**
   * {@inheritDoc}
   */
  public function getViewsData(): array {
    $data = parent::getViewsData();

    $data['website']['website_bulk_form'] = [
      'field' => [
        'id' => 'wholetthelogsout_entity_bulk_form',
      ],
      'help' => $this->t('Add a form element that lets you run operations on multiple websites.'),
      'title' => $this->t('Website operations bulk form'),
    ];

    return $data;
  }

}
