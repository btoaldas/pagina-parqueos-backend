<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\EmailService;
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

  public function __construct()
  {
    $this->authService = new AuthService();
    $this->emailService = new EmailService();
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
      $payload = $GLOBALS['payload'];
      Validator::with($payload, 'id')->required()->isInteger()->toInteger();

      $this->emailService->sendTokenToUpdate($payload['id']);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function validateToken()
  {
    try {
      $payload = $GLOBALS['payload'];
      Validator::with($payload, 'id')->required()->isInteger()->toInteger();

      $headers = getallheaders();
      Validator::with($headers, 'token-password')->required();

      $tokenPayload = JWT::validateToken($headers['token-password']);
      Validator::with($tokenPayload, ['id', 'info'])->required();
      Validator::with($tokenPayload, ['id'])->required()->isInteger()->toInteger();

      $this->authService->validatePasswordChange($payload, $tokenPayload);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function updatePassword()
  {
    try {
      $payload = $GLOBALS['payload'];
      Validator::with($payload, 'id')->required()->isInteger()->toInteger();

      $headers = getallheaders();
      Validator::with($headers, 'token-password')->required();

      $tokenPayload = JWT::validateToken($headers['token-password']);
      Validator::with($tokenPayload, ['id', 'info'])->required();
      Validator::with($tokenPayload, ['id'])->required()->isInteger()->toInteger();

      $body = Router::$body;

      Validator::with($body, 'password')
        ->required()
        ->isString()->minLength(4);

      $this->authService->validatePasswordChange($payload, $tokenPayload);

      $this->authService->updatePassword($payload['id'], $body['password']);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
