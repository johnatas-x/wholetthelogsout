<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\wholetthelogsout\Annotation\AlertType;
use Drupal\wholetthelogsout\Entity\AlertInterface;

/**
 * Provides the Alert type plugin manager.
 */
class AlertTypeManager extends DefaultPluginManager {

  /**
   * Constructs a new AlertTypeManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
      \Traversable $namespaces,
      CacheBackendInterface $cache_backend,
      ModuleHandlerInterface $module_handler
  ) {
    parent::__construct('Plugin/AlertType', $namespaces, $module_handler, AlertTypeInterface::class, AlertType::class);

    $this->alterInfo('alert_type_info');
    $this->setCacheBackend($cache_backend, 'alert_type_plugins');
  }

  /**
   * Create a plugin instance from an alert entity.
   *
   * This will automatically pass in the proper configuration stored in the
   * entity which is provided via the settings forms.
   *
   * @param \Drupal\wholetthelogsout\Entity\AlertInterface $alert
   *   An alert entity.
   * @param string|null $plugin_id
   *   The plugin ID, or NULL to load the type from the Alert.
   *
   * @return object
   *   An alert type plugin instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function createInstanceFromAlert(AlertInterface $alert, ?string $plugin_id = NULL): object {
    $settings = [];

    // Determine the plugin ID.
    $plugin_id = $plugin_id ?: (string) $alert->getType();

    $value = $alert->getSettings();

    // Check if there are alert settings and if this plugin has settings stored.
    if (!empty($value[$plugin_id])) {
      // Use these settings.
      $settings = $value[$plugin_id];
    }

    // Create an instance.
    return $this->createInstance($plugin_id, $settings);
  }

}
