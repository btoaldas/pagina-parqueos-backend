<?php

namespace App\Utils;

class Response
{
  public static function json($data, $message = "Success", $statusCode = 200)
  {
    http_response_code($statusCode);
    header('Content-type: application/json; Charset=utf-8');
    header_remove('X-Powered-By');
    echo json_encode(["ok" => $statusCode < 400, "statusCode" => $statusCode, "message" => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
  }

  public static function image(string $path)
  {
    $mime = mime_content_type($path);

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));

    http_response_code(200);
    readfile($path);
    exit;
  }
}
