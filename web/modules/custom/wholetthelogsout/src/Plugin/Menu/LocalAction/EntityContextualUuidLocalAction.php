<?php

namespace Drupal\wholetthelogsout\Plugin\Menu\LocalAction;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Menu\LocalActionDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Base class to add a local action entity contextual link.
 *
 * This appends an entity being viewed of a given type as a query parameter.
 *
 * For example, if you're viewing a website, you can append ?website={uuid}
 * to the link URL.
 */
abstract class EntityContextualUuidLocalAction extends LocalActionDefault {

  /**
   * RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * Constructs a EntityContextualLocalAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  final public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteProviderInterface $route_provider, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_provider);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): EntityContextualUuidLocalAction|ContainerFactoryPluginInterface|LocalActionDefault|static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(RouteMatchInterface $route_match): array {
    $options = parent::getOptions($route_match);
    $request = $this->requestStack->getCurrentRequest();

    if ($request instanceof Request) {
      $entity = $request->attributes->get($this->getEntityType());

      if ($entity) {
        $options['query'][$this->getEntityType()] = ($entity instanceof EntityInterface) ? $entity->uuid() : $entity;
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeTags(parent::getCacheContexts(), ['url.path']);
  }

  /**
   * Return the entity type to capture.
   *
   * This should be the entity being viewed.
   *
   * @return string
   *   The entity type.
   */
  abstract public function getEntityType(): string;

}
