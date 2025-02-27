<?php

namespace App\Utils;

use Exception;
use RuntimeException;

class HttpError extends RuntimeException
{
  private $statusCode;

  public function __construct($message, $statusCode, $errorCode = 0, Exception $previus = null)
  {
    $this->statusCode = $statusCode;
    parent::__construct($message, $errorCode, $previus);
  }

  public function getStatusCode()
  {
    return $this->statusCode;
  }

  public function __toString(): string
  {
    return __CLASS__ . ": [{$this->statusCode}]: {$this->message}";
  }

  public static function NotFound($message): HttpError
  {
    return new self($message, 404);
  }

  public static function BadRequest($message): HttpError
  {
    return new self($message, 400);
  }

  public static function NotAuthorized($message): HttpError
  {
    return new self($message, 401);
  }

  public static function InternalServer($message): HttpError
  {
    return new self($message, 500);
  }
}
