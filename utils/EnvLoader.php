<?php

namespace App\Utils;

use Exception;

class EnvLoader
{
  private static $loaded = false;

  public static function load($path = '.env')
  {
    if (self::$loaded)
      return;

    if (!file_exists($path))
      throw new Exception("Project not configured");

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
      if (strpos(trim($line), '#') === 0)
        continue;

      list($key, $value) = explode('=', $line, 2);

      $key = trim($key);
      $value = trim($value);

      if (!array_key_exists($key, $_ENV)) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
      }
    }

    self::$loaded = true;
  }
}
