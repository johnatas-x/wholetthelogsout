<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm as CoreContentEntityDeleteForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\wholetthelogsout\Entity\EntityBaseInterface;

/**
 * Base form for deleting custom entities.
 */
class ContentEntityDeleteForm extends CoreContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getRedirectUrl(): Url {
    // Check for a parent.
    $entity = $this->getEntity();
    assert($entity instanceof EntityBaseInterface);

    $parent = $entity->getParent();

    if ($parent instanceof EntityInterface) {
      return $parent->toUrl('canonical');
    }

    return Url::fromRoute('<front>');
  }

}
