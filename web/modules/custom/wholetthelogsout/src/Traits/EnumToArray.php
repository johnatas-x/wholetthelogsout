<?php

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Traits;

/**
 * Trait for array methods on current Enum.
 */
trait EnumToArray {

  /**
   * Returns an array with all Enum's names.
   *
   * @return array
   *   Enum's names.
   */
  public static function names(): array {
    return array_column(self::cases(), 'name');
  }

  /**
   * Returns an array with all Enum's values.
   *
   * @return array
   *   Enum's values.
   */
  public static function values(): array {
    return array_column(self::cases(), 'value');
  }

  /**
   * Returns an array with all Enum's names => values.
   *
   * @return array
   *   Enum's names and values mapping.
   */
  public static function array(): array {
    return array_combine(self::values(), self::names());
  }

}
