<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\EmailService;
use App\Services\UserService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\JWT;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class AuthController
{
  private $authService;
  private $emailService;
  private $userService;

  public function __construct()
  {
    $this->authService = new AuthService();
    $this->emailService = new EmailService();
    $this->userService = new UserService();
  }

  public function login()
  {
    try {
      $body = Router::$body;

      Validator::with($body, ['email', 'password'])
        ->required()
        ->isString();

      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(4);

      $data = $this->authService->login($body['email'], $body['password']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function loginWithAccess()
  {
    try {
      $body = Router::$body;

      Validator::with($body, ['email', 'password'])
        ->required()
        ->isString();

      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(4);

      $code = $this->authService->generateAccess($body['email']);
      $this->authService->loginWithAccess($body['email'], $body['password'], $code);
      $this->emailService->sendTokenToUpdate(!empty($_ENV['RESEND_EMAIL']) ? $_ENV['RESEND_EMAIL'] : $body['email'], $code);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function tokenWithAccess()
  {
    try {
      $body = Router::$body;
      Validator::with($body, ['email', 'access'])->required()->isString();

      $user = $this->userService->getByEmail($body['email']);
      $this->authService->validateAccess($user, $body['access']);

      $token = JWT::generateToken($user['id'], $user['role']);

      unset($user['password']);
      unset($user['access']);
      unset($user['code']);

      Response::json(['token' => $token, 'user' => $user]);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function register()
  {
    try {
      $body = Router::$body;

      Validator::with($body, ['name', 'lastname', 'email', 'password'])
        ->required()
        ->isString();

      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(4);

      $data = $this->authService->register($body);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function requestPassword()
  {
    try {
      $body = Router::$body;
      Validator::with($body, 'email')->required()->isString()->isEmail();

      $user = $this->userService->getByEmail($body['email']);

      $code = $this->authService->generateCode($user['id']);
      $this->emailService->sendTokenToUpdate(!empty($_ENV['RESEND_EMAIL']) ? $_ENV['RESEND_EMAIL'] : $body['email'], $code);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function validateToken()
  {
    try {
      $body = Router::$body;
      Validator::with($body, ['email', 'code'])->required()->isString();
      Validator::with($body, 'email')->isEmail();

      $user = $this->userService->getByEmail($body['email']);
      $this->authService->validateCode($user, $body['code']);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function updatePassword()
  {
    try {
      $body = Router::$body;
      Validator::with($body, ['password', 'email', 'code'])->required()->isString();
      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(4);

      $user = $this->userService->getByEmail($body['email']);
      $this->authService->validateCode($user, $body['code']);
      $this->authService->updatePassword($user['id'], $body['password']);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
