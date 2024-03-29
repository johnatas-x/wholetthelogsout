<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder as CoreEntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a class to build a listing of Custom entities.
 */
class EntityListBuilder extends CoreEntityListBuilder {

  /**
   * {@inheritDoc}
   */
  public function buildHeader(): array {
    $header = [
      'id' => $this->t('ID'),
      'name' => $this->t('Label'),
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function buildRow(EntityInterface $entity): array {
    $label = $entity->label();

    if (!is_string($label)) {
      $label = $label instanceof TranslatableMarkup
          ? $label->render()
          : '';
    }

    $row = [
      'id' => $entity->id(),
      'name' => Link::fromTextAndUrl($label, $entity->toUrl()),
    ];

    return $row + parent::buildRow($entity);
  }

}
