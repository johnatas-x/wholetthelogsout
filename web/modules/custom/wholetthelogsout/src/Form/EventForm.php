<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Event edit forms.
 */
class EventForm extends ContentEntityForm {

  /**
   * {@inheritDoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $uuid = $this->entity->uuid();
    $status = parent::save($form, $form_state);

    $this->messenger()->addMessage($this->t('%action event %uuid.', [
      '%action' => $status === SAVED_NEW ? 'Created' : 'Saved',
      '%uuid' => $uuid,
    ]));

    $form_state->setRedirect('entity.event.canonical', ['event' => $uuid]);

    return $status;
  }

}
