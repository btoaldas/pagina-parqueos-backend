<?php

namespace App\Services;

use App\Config\EmailClient;
use App\Utils\JWT;
use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
  private PHPMailer $client;

  public function __construct()
  {
    $this->client = EmailClient::getClient();
  }

  public function sendTokenToUpdate(string $email, string $code)
  {
    $mail = $this->client;

    $mail->isHTML(true);

    $mail->setFrom('onboarding@resend.dev');
    $mail->addAddress($email);
    $mail->Subject = 'Hello World';
    $mail->Body = <<<EOT
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <style>
      * {
        padding: 0;
      }
      .container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: #222;
      }
      .text {
        color: white;
      }
      .token {
        background-color: #444;
        color: #aaa;
        padding: 12px;
        border-radius: 12px;
        box-shadow: 2px 2px 8px rgba(2, 3, 1, 50%);
      }
      .token:hover {
        background-color: #333;
        color: #ddd;
      }
    </style>
    </head>
    <body>
    <div class='container'>
      <p class='text'>Copy this Code</p>
      <p class='token'>$code</p>
    </div>
    <body>
    </html>
    EOT;

    $mail->send();
  }
}
