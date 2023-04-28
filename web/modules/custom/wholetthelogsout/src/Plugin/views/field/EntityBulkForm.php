<?php

namespace Drupal\wholetthelogsout\Plugin\views\field;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a custom entity operations bulk form element.
 *
 * @ViewsField("wholetthelogsout_entity_bulk_form")
 */
class EntityBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage(): string|TranslatableMarkup {
    return $this->t('Nothing selected.');
  }

}
