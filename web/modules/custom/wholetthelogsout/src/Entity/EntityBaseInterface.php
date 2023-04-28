<?php

namespace Drupal\wholetthelogsout\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining all custom entities.
 */
interface EntityBaseInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the entity name.
   *
   * @return string
   *   Name of the entity.
   */
  public function getName(): string;

  /**
   * Sets the entity name.
   *
   * @param string $name
   *   The entity name.
   */
  public function setName(string $name): void;

  /**
   * Gets the creation timestamp.
   *
   * @return int
   *   Creation timestamp.
   */
  public function getCreatedTime(): int;

  /**
   * Sets the creation timestamp.
   *
   * @param int $timestamp
   *   The creation timestamp.
   */
  public function setCreatedTime(int $timestamp): void;

  /**
   * Get the parent entity reference field name.
   *
   * @return string|null
   *   The entity reference field name or NULL if this entity does not have one.
   */
  public static function getParentReferenceFieldName(): ?string;

  /**
   * Get the parent entity reference entity type ID.
   *
   * This should automatically derive a value using
   * getParentReferenceFieldName().
   *
   * @return mixed
   *   The parent entity reference target entity type ID, or NULL if there is
   *   not one defined.
   */
  public function getParentReferenceEntityTypeId(): mixed;

  /**
   * Get the parent entity, if one is defined and present.
   *
   * This searches either one or infinite levels up the relationship tree.
   *
   * @param string|null $parent_entity_type
   *   The parent entity type to search for. If omitted, the type used in
   *   getParentReferenceFieldName() will be used which is the immediate parent
   *   of this entity. If you specific a different type, this function will look
   *   at parent's parent until the target entity type is found.
   *
   * @return mixed|null
   *   The parent entity, if found, or NULL.
   */
  public function getParent(string $parent_entity_type = NULL): mixed;

}
