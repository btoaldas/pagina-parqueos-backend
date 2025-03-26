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

    #$mail->setFrom('onboarding@resend.dev');
    $mail->CharSet = "UTF-8";
    $mail->setFrom('quipux@puyo.gob.ec');
    $mail->addAddress($email);
    // Adjuntar la imagen embebida (asegúrate de que la ruta sea correcta)
    $logoPath = $_SERVER['DOCUMENT_ROOT'].'/pagina-parqueos-backend/imagenes/logo.png';
    $mail->addEmbeddedImage($logoPath, 'logoimg', 'logo.png');
    $mail->Subject = 'Hola, aquí tienes tu código de verificación';
    $mail->Body = <<<EOT
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="UTF-8">
        <title>Código de Verificación</title>
        <style>
          /* Estilos generales */
          body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
          }
          .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
          }
          .header {
            background-color: #007BFF;
            text-align: center;
            padding: 20px;
          }
          .header img {
            max-width: 150px;
          }
          .content {
            padding: 30px;
            text-align: center;
            color: #333;
          }
          .token {
            display: inline-block;
            background-color: #0056b3;
            color: #fff;
            font-size: 24px;
            padding: 10px 20px;
            margin: 20px 0;
            border-radius: 6px;
          }
          .footer {
            background-color: #007BFF;
            color: #fff;
            text-align: center;
            padding: 10px;
            font-size: 12px;
          }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <img src="cid:logoimg" alt="Logo">
          </div>
          <div class="content">
            <h2>¡Bienvenido!</h2>
            <p>Para completar el proceso, utiliza el siguiente código de verificación:</p>
            <div class="token">$code</div>
            <p>Si no solicitaste este correo, ignóralo.</p>
          </div>
          <div class="footer">
            &copy; 2025 SEROTP - Parqueos. Todos los derechos reservados.
          </div>
        </div>
      </body>
      </html>
      EOT;

    $mail->send();
  }
}
