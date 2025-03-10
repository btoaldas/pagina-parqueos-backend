<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\FineService;
use App\Services\TicketService;
use App\Services\UserService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class ProfileController
{
  private $userService;
  private $ticketService;
  private $fineService;

  public function __construct()
  {
    $this->userService = new UserService();
    $this->ticketService = new TicketService();
    $this->fineService = new FineService();
  }

  public function getProfile()
  {
    try {
      $payload = $GLOBALS['payload'];
      Validator::with($payload, 'id')->required()->isInteger()->toInteger();

      $user = $this->userService->getOne($payload['id']);

      Response::json($user);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
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

  public function getTicketsFromUser()
  {
    try {
      $payload = $GLOBALS['payload'];
      $tickets = $this->ticketService->getTicketsFromUser($payload['id']);

      Response::json($tickets);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getFinesFromUser()
  {
    try {
      $payload = $GLOBALS['payload'];
      $tickets = $this->fineService->getFinesFromUser($payload['id']);
      Response::json($tickets);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
