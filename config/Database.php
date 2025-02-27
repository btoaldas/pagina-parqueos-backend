<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
  private static $connection;

  public static function getConnection()
  {
    if (!self::$connection) {
      $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
      $dbname = $_ENV['DB_NAME'] ?? 'project';
      $username = $_ENV['DB_USER'] ?? 'user';
      $password = $_ENV['DB_PASS'] ?? 'password';

      try {
        self::$connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
      }
    }

    return self::$connection;
  }
}
