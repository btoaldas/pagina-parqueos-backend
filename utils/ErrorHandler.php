<?php

namespace App\Utils;

class ErrorHandler
{
  public static function handlerError($message, $statusCode = 400)
  {
    Response::json(null, $message, $statusCode);
  }
}
