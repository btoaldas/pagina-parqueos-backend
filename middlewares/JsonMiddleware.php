<?php

namespace App\Middlewares;

use App\Utils\HttpError;
use App\Utils\Router;

class JsonMiddleware
{
  public function json()
  {
    if ($_SERVER['CONTENT_TYPE'] !== 'application/json')
      throw HttpError::BadRequest('Content-Type must be application/json');

    $body = file_get_contents('php://input');

    $decodedBody = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE)
      throw HttpError::BadRequest('Invalid JSON in request body');

    Router::$body = $decodedBody;
  }
}
