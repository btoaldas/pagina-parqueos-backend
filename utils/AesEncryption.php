<?php

namespace App\Utils;

use Exception;

class AesEncryption
{
  public static function encypt(string $value): string
  {
    $clave = $_ENV['AES_KEY'];
    $method = 'aes-256-cbc';

    $iv_length = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($iv_length);

    $cifrado = openssl_encrypt($value, $method, $clave, 0, $iv);

    $cifrado_con_iv = base64_encode($iv . $cifrado);

    return $cifrado_con_iv;
  }

  public static function decrypt(string $value): string
  {
    $clave = $_ENV['AES_KEY'];
    $method = 'aes-256-cbc';

    $iv_length = openssl_cipher_iv_length($method);
    $cifrado_con_iv = base64_decode($value);

    $iv_extraido = substr($cifrado_con_iv, 0, $iv_length);
    $cifrado_extraido = substr($cifrado_con_iv, $iv_length);

    // Descifrar los datos
    $descifrado = openssl_decrypt($cifrado_extraido, $method, $clave, 0, $iv_extraido);

    return $descifrado;
  }
}
