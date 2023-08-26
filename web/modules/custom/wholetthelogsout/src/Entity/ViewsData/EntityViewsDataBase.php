<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity\ViewsData;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for custom entities.
 */
abstract class EntityViewsDataBase extends EntityViewsData implements EntityViewsDataBaseInterface {

  /**
   * {@inheritDoc}
   */
  public function getViewsData(): array {
    $data = parent::getViewsData();
    $entity = $this->getEntity();

    $data[$entity->value][$entity->value . '_bulk_form'] = [
      'field' => [
        'id' => 'wholetthelogsout_entity_bulk_form',
      ],
      'help' => $this->t(
        'Add a form element that lets you run operations on multiple @entities.',
        [
          '@entities' => $entity->plural(),
        ]
      ),
      'title' => $this->t(
        '@entity operations bulk form',
        [
          '@entity', $entity->capital(),
        ]
      ),
    ];

    return $data;
  }

}
