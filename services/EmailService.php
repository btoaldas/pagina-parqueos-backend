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

  public function sendTokenToUpdate(int $id)
  {
    $token = JWT::generateJwt(['info' => 'password-update', 'id' => $id], 3600);

    $mail = $this->client;

    $mail->isHTML(true);

    $mail->setFrom('onboarding@resend.dev');
    $mail->addAddress('gonzalesdlcgrober@gmail.com');
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
      <p class='token'>$token</p>
    </div>
    <body>
    </html>
    EOT;

    $mail->send();

    return $token;
  }
}
