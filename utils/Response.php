<?php

class Response
{
  public static function json($data, $message = "Success", $statusCode = 200)
  {
    http_response_code($statusCode);
    header('Content-type: application/json');
    echo json_encode(["statusCode" => $statusCode, "message" => $message, 'data' => $data]);
    exit;
  }
}
