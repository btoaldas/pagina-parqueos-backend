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

    Validator::with(
      $_ENV,
      [
        'PATH_BASE',
        'PATH_STORAGE',
        'DB_HOST',
        'DB_PORT',
        'DB_NAME',
        'DB_USER',
        'DB_PASS',
        'JWT_SECRET',
        'JWT_EXPIRE',
      ]
    )->required();

    Validator::with($_ENV, [
      'JWT_EXPIRE',
      'DB_PORT',
    ])->toInteger();

    self::$loaded = true;
  }
}
