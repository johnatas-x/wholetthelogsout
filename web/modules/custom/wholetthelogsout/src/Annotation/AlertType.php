<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines an Alert type item annotation object.
 *
 * @see \Drupal\wholetthelogsout\Plugin\AlertTypeManager
 * @see plugin_api
 *
 * @Annotation
 */
class AlertType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public string $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public Translation $label;

}
