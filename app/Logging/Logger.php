<?php

namespace App\Logging;

use Illuminate\Support\Facades\Log;

/**
 * Logger class for application-wide logging.
 */
class Logger
{
  /**
   * Log an informational message.
   *
   * @param string $message
   * @param array $context
   */
  public static function info(string $message, array $context = []): void
  {
    Log::info($message, $context);
  }

  /**
   * Log a warning message.
   *
   * @param string $message
   * @param array $context
   */
  public static function warning(string $message, array $context = []): void
  {
    Log::warning($message, $context);
  }
  /**
   * Log an error message.
   *
   * @param string $message
   * @param array $context
   */
  public static function error(string $message, array $context = []): void
  {
    Log::error($message, $context);
  }

  /**
   * Log a debug message.
   *
   * @param string $message
   * @param array $context
   */
  public static function debug(string $message, array $context = []): void
  {
    if (config('app.debug')) {
      Log::debug($message, $context);
    }
  }

  /**
   * Log a critical message.
   *
   * @param string $message
   * @param array $context
   */
  public static function critical(string $message, array $context = []): void
  {
    Log::critical($message, $context);
  }
}
