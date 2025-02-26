<?php

require_once __DIR__ . '/Response.php';

class ErrorHandler
{
  public static function handlerError($message, $statusCode = 400)
  {
    Response::json(null, $message, $statusCode);
  }
}
