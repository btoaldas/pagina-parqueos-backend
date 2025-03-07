<?php

namespace App\Utils;

class Template
{
  public static function htmlMainReport(array $data)
  {
    $body = '';

    $body = $body . "
    <div class='users'>
      <h3>Multas Pendientes</h3>
      <div>
        <p>Cantidad de multas pendientes</p>
        <p>{$data['fees']['total']}</p>
      </div>
      <div>
        <p>Multas por cobras</p>
        <p>$ {$data['fees']['amount']}</p>
      </div>
    </div>
    ";

    $body = $body . "
    <div class='users'>
      <h3>Ingresos del día</h3>
      <div>
        <p>Porcentaje ocupado</p>
        <p>{$data['income_today']['total']}</p>
      </div>
    </div>
    ";

    $body = $body . "
      <div class='users'>
        <h3>Espacios Ocupados</h3>
        <div>
          <p>Total de espacios</p>
          <p>{$data['spaces_taken']['total']}</p>
        </div>
        <div>
          <p>Espacios ocupados</p>
          <p>{$data['spaces_taken']['taken']}</p>
        </div>
        <div>
          <p>Porcentaje ocupado</p>
          <p>{$data['spaces_taken']['percent']}%</p>
        </div>
      </div>
    ";

    $body = $body . "
      <div class='users'>
        <h3>Usuarios Activos</h3>
        <div>
          <p>Mes Actual</p>
          <p>{$data['active_users']['current_month']}</p>
        </div>
        <div>
          <p>Mes Anterior</p>
          <p>{$data['active_users']['last_month']}</p>
        </div>
      </div>
    ";

    $html = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>PDF Example</title>
      <style>
        * {
          padding: 0;
          margin: 0;
          font-family: 'Courier New', Courier, monospace;
        }
        .hero {
          display: flex;
          background-color: #4af;
          min-height: 64px;
          color: black;
        }
        .hero > h1 {
          font-size: 36px;
          color: #124;
        }
        .users {
          background-color: #e6e6e6;
        }
      </style>
    </head>
    <body class='wrapper'>
    <div class='hero'>
      <h1>Reportes y estadisticas</h1>
    </div>
    $body
    </body>
    </html>
    ";

    return $html;
  }
}
