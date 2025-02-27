<?php

require_once __DIR__ . '/../services/AuthService.php';

require_once __DIR__ . '/../utils/Validator.php';

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/ErrorHandler.php';

require_once __DIR__ . '/../utils/HttpError.php';

class AuthController
{
  private $authService;

  public function __construct()
  {
    $this->authService = new AuthService();
  }

  public function login()
  {
    try {
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::with($body, ['email', 'password'])
        ->required()
        ->isString();

      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(8);

      $data = $this->authService->login($body['email'], $body['password']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function register()
  {
    try {
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::with($body, ['name', 'lastname', 'email', 'password'])
        ->required()
        ->isString();

      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(8);

      $data = $this->authService->register($body);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
