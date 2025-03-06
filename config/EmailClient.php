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

      self::$client->Host = 'smtp.resend.com';
      self::$client->SMTPAuth = true;
      self::$client->Username = 'resend';
      self::$client->Password = $_ENV['RESEND_TOKEN'];
      self::$client->SMTPSecure = 'tls';
      self::$client->Port = 587;
    }

    return self::$client;
  }
}
