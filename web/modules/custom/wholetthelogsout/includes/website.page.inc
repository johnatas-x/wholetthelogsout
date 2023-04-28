<?php

/**
 * @file
 * Page callback for Website entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Website templates.
 *
 * Default template: website.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_website(array &$variables): void {
  // Fetch Website Entity Object.
  $website = $variables['elements']['#website'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
