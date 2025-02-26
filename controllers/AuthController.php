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
      $data = json_decode(file_get_contents('php://input'), true);

      Validator::validateRequiredFields($data, ['email', 'password']);
      Validator::validateEmail($data['email']);
      Validator::validatePassword($data['password']);

      $email = $data['email'];
      $password = $data['password'];

      $data = $this->authService->login($email, $password);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function register()
  {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      Validator::validateRequiredFields($data, ['name', 'lastname', 'email', 'password']);
      Validator::validateEmail($data['email']);
      Validator::validatePassword($data['password']);

      $data = $this->authService->register($data);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function test()
  {
    Response::json(['message' => $GLOBALS['payload']]);
  }
}
