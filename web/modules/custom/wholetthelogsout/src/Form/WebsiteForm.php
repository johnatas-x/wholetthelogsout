<?php

namespace Drupal\wholetthelogsout\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Website edit forms.
 */
class WebsiteForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    $this->messenger()->addMessage($this->t('%action the %label website.', [
      '%action' => ($status === SAVED_NEW) ? 'Created' : 'Saved',
      '%label' => $entity->label(),
    ]));

    $form_state->setRedirect('entity.website.canonical', ['website' => $entity->uuid()]);

    return $status;
  }

}
