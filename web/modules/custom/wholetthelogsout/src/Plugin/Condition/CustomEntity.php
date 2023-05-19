<?php

namespace Drupal\wholetthelogsout\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Custom entity' condition.
 *
 * @Condition(
 *   id = "custom_entity",
 *   label = @Translation("Custom entity"),
 * )
 */
class CustomEntity extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * Constructs a CustomEntity condition plugin.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   */
  final public function __construct(RouteMatchInterface $route_match, array $configuration, string $plugin_id, mixed $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): CustomEntity|ContainerFactoryPluginInterface|static {
    return new static(
      $container->get('current_route_match'),
      $configuration,
      $plugin_id,
      $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return ['types' => []] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Custom entity types'),
      '#default_value' => $this->configuration['types'],
      '#options' => [
        'website' => $this->t('Website'),
      ],
      '#description' => $this->t('Specify which custom entity types you want this block to appear on. It will only show on the canonical path.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValue('types');

    if (is_array($values)) {
      $this->configuration['types'] = array_filter($values);
    }

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary(): TranslatableMarkup {
    $types = implode(', ', $this->configuration['types']);

    return $this->t('Return true on the following entity canonical paths: @types', ['@types' => $types]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(): bool {
    // Get the route name.
    $route_name = $this->routeMatch->getRouteName();

    // Iterate the active entity types.
    foreach ($this->configuration['types'] as $type) {
      // Check for a canonical match.
      if ($route_name === "entity.$type.canonical") {
        return !$this->isNegated();
      }
    }

    return $this->isNegated();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    $contexts = parent::getCacheContexts();
    $contexts[] = 'url.path';

    return $contexts;
  }

}
