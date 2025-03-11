<?php

namespace App\Middlewares;

class CorsMiddleware
{
  public function cors()
  {
    header('Access-Control-Allow-Origin: ' . $_ENV['ALLOW_ORIGIN']);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Credentials: true');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
      header('Access-Control-Allow-Headers: *');
      exit(0);
    }
  }
}
