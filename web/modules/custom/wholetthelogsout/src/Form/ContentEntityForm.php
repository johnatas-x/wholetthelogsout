<?php

namespace Drupal\wholetthelogsout\Form;

use Drupal\Core\Entity\ContentEntityForm as CoreContentEntityForm;

/**
 * Form controller base class for custom content entity forms.
 */
class ContentEntityForm extends CoreContentEntityForm {

  /**
   * Helper function to add details wrappers to a form.
   *
   * @param array &$form
   *   The form to alter.
   * @param string $wrapper
   *   The wrapper ID prefix.
   * @param mixed $title
   *   The title to add to the details element.
   * @param array $keys
   *   An array of form keys to add to the details element.
   */
  public function addDetails(array &$form, string $wrapper, mixed $title, array $keys): void {
    $weight = &drupal_static(__METHOD__, 0);

    $form["{$wrapper}_wrapper"] = [
      '#type' => 'details',
      '#title' => $title,
      '#open' => TRUE,
      '#weight' => $weight,
    ];

    foreach ($keys as $key) {
      $form["{$wrapper}_wrapper"][$key] = $form[$key];
      unset($form[$key]);
    }

    $weight++;
  }

}
