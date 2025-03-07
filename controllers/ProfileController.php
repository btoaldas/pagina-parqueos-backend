<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\UserService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class ProfileController
{
  private $userService;

  public function __construct()
  {
    $this->userService = new UserService();
  }

  public function update()
  {
    try {
      $payload = $GLOBALS['payload'];
      Validator::with($payload, ['id', 'role'])->required();
      Validator::with($payload, 'id')->isInteger()->toInteger();
      $body = Router::$body;
      Validator::with($body, ['name', 'lastname'])->required()->isString()->minLength(2);

      $value = $this->userService->updateProfile($payload['id'], $body['name'], $body['lastname']);

      Response::json($value);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function updatePassword()
  {
    try {
      $payload = $GLOBALS['payload'];
      Validator::with($payload, ['id', 'role'])->required();
      Validator::with($payload, 'id')->isInteger()->toInteger();

      $body = Router::$body;
      Validator::with($body, ['password', 'new-password'])->required()->isString()->minLength(4);

      $this->userService->updatePassword($payload['id'], $body['password'], $body['new-password']);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
