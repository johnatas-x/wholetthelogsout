<?php

namespace Drupal\wholetthelogsout\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Website entities.
 */
class WebsiteViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData(): array {
    $data = parent::getViewsData();

    $data['website']['website_bulk_form'] = [
      'title' => $this->t('Website operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple websites.'),
      'field' => [
        'id' => 'wholetthelogsout_entity_bulk_form',
      ],
    ];

    return $data;
  }

}
