<?php

namespace App\Config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailClient
{
  private static $client;

  public static function getClient()
  {
    if (!self::$client) {
      self::$client = new PHPMailer(true);

      self::$client->isSMTP();

      self::$client->Host = $_ENV['STMP_HOST'];
      self::$client->Port = $_ENV['STMP_PORT'];
      self::$client->SMTPAuth = true;
      self::$client->SMTPSecure = $_ENV['STMP_SECURE'];
      self::$client->Username = $_ENV['STMP_USERNAME'];
      self::$client->Password = $_ENV['STMP_PASSWORD'];
    }

    return self::$client;
  }
}
