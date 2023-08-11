<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Content entity base class for custom entities.
 */
abstract class EntityBase extends ContentEntityBase implements EntityBaseInterface {

  /**
   * {@inheritDoc}
   */
  public function getName(): string {
    $name = $this->get('name')->value;

    return is_string($name)
        ? $name
        : '';
  }

  /**
   * {@inheritDoc}
   */
  public function setName($name): void {
    $this->set('name', $name);
  }

  /**
   * {@inheritDoc}
   */
  public function getCreatedTime(): int {
    $created = $this->get('created')->value;

    return is_int($created)
        ? $created
        : 0;
  }

  /**
   * {@inheritDoc}
   */
  public function setCreatedTime($timestamp): void {
    $this->set('created', $timestamp);
  }

  /**
   * {@inheritDoc}
   */
  public function getOwner(): UserInterface {
    $owner = $this->get('user_id')->entity;
    assert($owner instanceof UserInterface);

    return $owner;
  }

  /**
   * {@inheritDoc}
   */
  public function getOwnerId(): ?int {
    $target = $this->get('user_id')->target_id;

    return is_numeric($target)
        ? (int) $target
        : NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function setOwnerId($uid): self|EntityOwnerInterface|static {
    $this->set('user_id', $uid);

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function setOwner(UserInterface $account): self|EntityOwnerInterface|static {
    $this->set('user_id', $account->id());

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function getParentReferenceEntityTypeId(): mixed {
    // Get the parent reference field name.
    $parent_field_name = self::getParentReferenceFieldName();

    if ($parent_field_name) {
      return $this->getFieldDefinition($parent_field_name)?->getSetting('target_type');
    }

    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getParent($parent_entity_type = NULL): mixed {
    // Get the parent reference field name.
    $parent_field_name = self::getParentReferenceFieldName();

    // Stop if this entity does not have a parent.
    if (!$parent_field_name) {
      return NULL;
    }

    // Attempt to load the parent entity.
    $parent = $this->get($parent_field_name)->entity;
    assert($parent instanceof EntityBaseInterface);

    // Check if this is the correct type.
    if (!$parent_entity_type || ($parent->getEntityTypeId() === $parent_entity_type)) {
      return $parent;
    }

    // Continue searching.
    return $parent->getParent($parent_entity_type);
  }

  /**
   * {@inheritDoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values): void {
    parent::preCreate($storage, $values);

    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User')->render())
      ->setDescription(t('The user ID of owner of the entity.')->render())
      ->setRequired(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId')
      ->setSetting('handler', 'default');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created')->render())
      ->setDescription(t('The time that the entity was created.')->render());

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed')->render())
      ->setDescription(t('The time that the entity was last edited.')->render());

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public static function getParentReferenceFieldName(): ?string {
    return NULL;
  }

  /**
   * Default value callback for 'user_id' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId(): array {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * {@inheritDoc}
   */
  protected function invalidateTagsOnSave($update): void {
    // We do not use list cache tags for these entities because they are all
    // only accessible by the owning user, and the entity tags contain
    // contextual tags already.
    $tags = [];

    if ($this->hasLinkTemplate('canonical')) {
      // Creating or updating an entity may change a cached 403 or 404 response.
      $tags = Cache::mergeTags($tags, ['4xx-response']);
    }

    // Also invalidate its unique cache tag.
    // Core only does this for existing entities but we need it done for all.
    $tags = Cache::mergeTags($tags, $this->getCacheTagsToInvalidate());

    // Invalidate the tags.
    Cache::invalidateTags($tags);
  }

  /**
   * {@inheritDoc}
   */
  protected function urlRouteParameters($rel): array {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    // Switch the entity ID out for the UUID.
    if (isset($uri_route_parameters[$this->getEntityTypeId()])) {
      $uri_route_parameters[$this->getEntityTypeId()] = $this->uuid();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritDoc}
   */
  protected static function invalidateTagsOnDelete(EntityTypeInterface $entity_type, array $entities): void {
    // Skip the list tags. See invalidateTagsOnSave().
    $tags = [];

    foreach ($entities as $entity) {
      $tags = Cache::mergeTags($tags, $entity->getCacheTagsToInvalidate());
    }

    Cache::invalidateTags($tags);
  }

}
