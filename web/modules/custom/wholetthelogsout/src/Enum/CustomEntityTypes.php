<?php

/**
 * @file
 * Enum for custom entity Types defined in this module.
 */

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Enum;

/**
 * Available custom entity types IDs.
 */
enum CustomEntityTypes: string {

  case Event = 'event';
  case Website = 'website';

  /**
   * Plural wording.
   *
   * @return string
   *   The entity type plural wording.
   */
  public function plural(): string {
    return match ($this) {
      self::Event => 'events',
      self::Website => 'websites',
    };
  }

  /**
   * Wording with the first capital letter.
   *
   * @return string
   *   The entity type wording with the first capital letter.
   */
  public function capital(): string {
    return match ($this) {
      self::Event => 'Event',
      self::Website => 'Website',
    };
  }

  /**
   * Plural wording with the first capital letter.
   *
   * @return string
   *   The entity type plural wording with the first capital letter.
   */
  public function capitalPlural(): string {
    return match ($this) {
      self::Event => 'Events',
      self::Website => 'Websites',
    };
  }

}
