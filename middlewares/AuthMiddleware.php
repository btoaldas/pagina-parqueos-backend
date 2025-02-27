<?php

require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/ErrorHandler.php';

class AuthMiddlware
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
      return ErrorHandler::handlerError("Needs Authorization", 401);

    if (!preg_match('/Bearer\s(\S+)/', $authorization, $matches) || !$matches[1])
      return ErrorHandler::handlerError("Wrong Authorization");

    $jwt = JWT::validateToken($matches[1]);

    if (!$jwt)
      return ErrorHandler::handlerError("Not Authorized", 401);

    $GLOBALS['payload'] = $jwt;
  }

  public function onylRoles(...$roles)
  {
    $payload = $GLOBALS['payload'];

    if (!$payload)
      return ErrorHandler::handlerError("Not Authorized", 401);

    if (!is_array($roles))
      $roles = [$roles];

    foreach ($roles as $role) {
      if ($role === $payload['role'])
        return;
    }

    ErrorHandler::handlerError("Has not Role", 401);
  }

  public function validateExpiration()
  {
    $payload = $GLOBALS['payload'];

    if (!$payload)
      return ErrorHandler::handlerError("Not Authorized", 401);

    if (!isset($payload['exp']))
      return ErrorHandler::handlerError("El Token no contiene una fecha de expiracion");

    $currenTime = time();

    if ($payload['exp'] < $currenTime) {
      return ErrorHandler::handlerError("Token expired");
    }
  }
}
