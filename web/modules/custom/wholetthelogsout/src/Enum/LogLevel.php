<?php

/**
 * @file
 * Enum for log levels.
 *
 * @see \Drupal\Core\Logger\RfcLogLevel
 * @see \Psr\Log\LogLevel
 */

declare(strict_types = 1);

namespace Drupal\wholetthelogsout\Enum;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\wholetthelogsout\Traits\EnumToArray;

/**
 * Available log levels.
 */
enum LogLevel: string {

  use EnumToArray;

  case Emergency = 'emergency';
  case Alert = 'alert';
  case Critical = 'critical';
  case Error = 'error';
  case Warning = 'warning';
  case Notice = 'notice';
  case Info = 'info';
  case Debug = 'debug';

  /**
   * Numeric log level.
   *
   * @return int
   *   The numeric log level.
   *
   * @see \Drupal\Core\Logger\RfcLogLevel
   */
  public function numeric(): int {
    return match ($this) {
      self::Emergency => 0,
      self::Alert => 1,
      self::Critical => 2,
      self::Error => 3,
      self::Warning => 4,
      self::Notice => 5,
      self::Info => 6,
      self::Debug => 7,
    };
  }

  /**
   * Get RFC translatable markup.
   *
   * @return string
   *   The translatable rendered markup.
   */
  public function markup(): string {
    $rfc_log_level = new RfcLogLevel();
    $markups = $rfc_log_level::getLevels();

    return array_key_exists($this->numeric(), $markups)
      ? $markups[$this->numeric()]->render()
      : '';
  }

}
