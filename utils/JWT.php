<?php

namespace App\Utils;

class JWT
{
  public static function generateJwt(array $data, int $exp = 3600 * 24 * 7)
  {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $base64UrlHeader = self::base64UrlEncode($header);

    $payload = json_encode([...$data, 'exp' => time() + $exp]);
    $base64UrlPayload = self::base64UrlEncode($payload);

    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $_ENV['JWT_SECRET'], true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
  }

  public static function generateToken($userId, $role)
  {
    return self::generateJwt(['id' => $userId, 'role' => $role]);
  }

  public static function validateToken($token)
  {
    list($header, $payload, $signature) = explode('.', $token);
    $validSignature = hash_hmac('sha256', "$header.$payload", $_ENV['JWT_SECRET'], true);
    $base64UrlValidSignature = self::base64UrlEncode($validSignature);
    return $base64UrlValidSignature === $signature ? json_decode(self::base64UrlDecode($payload), true) : null;
  }

  public static function base64UrlEncode($value): string
  {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($value));
  }

  public static function base64UrlDecode($value)
  {
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $value));
  }
}
