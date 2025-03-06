<?php

namespace App\Middlewares;

use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\JWT;

class AuthMiddleware
{
  public function checkJwt(...$roles)
  {
    $this->checkAuth();
    $this->validateExpiration();
    $this->onylRoles(...$roles);
  }

  public function checkAuth()
  {
    $headers = getallheaders();
    $authorization = $headers['authorization'] ?? null;

    if (!$authorization)
      throw ErrorHandler::handlerError("Needs Authorization", 401);

    if (!preg_match('/Bearer\s(\S+)/', $authorization, $matches) || !$matches[1])
      throw ErrorHandler::handlerError("Wrong Authorization");

    $jwt = JWT::validateToken($matches[1]);

    if (!$jwt)
      throw ErrorHandler::handlerError("Not Authorized", 401);

    $GLOBALS['payload'] = $jwt;
  }

  public function onylRoles(...$roles)
  {
    $payload = $GLOBALS['payload'];

    if (!$payload)
      throw ErrorHandler::handlerError("Not Authorized", 401);

    if (!is_array($roles))
      $roles = [$roles];

    if (count($roles) === 0)
      return;

    foreach ($roles as $role) {
      if ($role === $payload['role'])
        return;
    }

    throw ErrorHandler::handlerError("Has not Role", 401);
  }

  public function validateExpiration()
  {
    $payload = $GLOBALS['payload'];

    if (!$payload)
      throw ErrorHandler::handlerError("Not Authorized", 401);

    if (!isset($payload['exp']))
      throw ErrorHandler::handlerError("El Token no contiene una fecha de expiracion");

    $currenTime = time();

    if ($payload['exp'] < $currenTime) {
      throw ErrorHandler::handlerError("Token expired");
    }
  }
}
