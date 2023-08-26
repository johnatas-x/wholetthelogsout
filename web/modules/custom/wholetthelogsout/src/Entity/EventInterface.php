<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event entities.
 */
interface EventInterface extends ContentEntityInterface, EntityOwnerInterface, EntityBaseInterface {

  /**
   * Get the type.
   *
   * @return string|null
   *   The event type, or NULL, if no value exists.
   */
  public function getType(): ?string;

  /**
   * Get the severity.
   *
   * @return string|null
   *   The event severity, or NULL, if no value exists.
   */
  public function getSeverity(): ?string;

  /**
   * Get the message.
   *
   * @return string|null
   *   The event message, or NULL, if no value exists.
   */
  public function getMessage(): ?string;

  /**
   * Get the user.
   *
   * Note that this is the user field, and not the Drupal user owner.
   *
   * @return string|null
   *   The event user, or NULL, if no value exists.
   */
  public function getUser(): ?string;

  /**
   * Get the URL.
   *
   * Note that this is the URL field, and not the entity URL.
   *
   * @return \Drupal\Core\Url|null
   *   The event URL, or NULL, if no value exists.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getUrl(): ?Url;

  /**
   * Get the expiration time.
   *
   * @return int|null
   *   The event expiration timestamp, or NULL, if no value exists.
   */
  public function getExpiration(): ?int;

}
