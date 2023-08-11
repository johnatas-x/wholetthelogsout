<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\ParamConverter;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Class EntityUuidConverter.
 *
 * Parameter converter for entity UUIDs.
 */
class EntityUuidConverter implements ParamConverterInterface {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected EntityRepositoryInterface $entityRepository;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Creates a new EntityUuidConverter instance.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(EntityRepositoryInterface $entity_repository) {
    $this->entityRepository = $entity_repository;
  }

  /**
   * Injects the language manager.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager to get the current content language.
   */
  public function setLanguageManager(LanguageManagerInterface $language_manager): void {
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function convert($value, $definition, $name, array $defaults) {
    if (!is_array($definition) || !is_string($value)) {
      return NULL;
    }

    // Load the entity.
    return $this
      ->entityRepository
      ->loadEntityByUuid($definition['entity_type_id'], $value);
  }

  /**
   * {@inheritDoc}
   */
  public function applies($definition, $name, Route $route): bool {
    return (
      is_array($definition) &&
      !empty($definition['type']) &&
      !empty($definition['entity_type_id']) &&
      ($definition['type'] === 'entity_uuid')
    );
  }

}
