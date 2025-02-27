<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class UserController
{
  private $userService;

  public function __construct()
  {
    $this->userService = new UserService();
  }

  public function getAll()
  {
    try {
      $queryparams = Router::$queryparams;

      Validator::with($queryparams)->limitOffset();

      $data = $this->userService->getAll($queryparams['limit'], $queryparams['offset']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getOne()
  {
    try {
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->userService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function create()
  {
    try {
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::with($body, ['name', 'lastname', 'email', 'password', 'state', 'role'])
        ->required()
        ->isString();
      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(8);

      $data = $this->userService->create($body);

      Response::json($data, "Created", 201);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function update()
  {
    try {
      $pathparams = Router::$pathparams;
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::with($body, ['name', 'lastname', 'email', 'password', 'state', 'role'])
        ->required()
        ->isString();
      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(8);

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->userService->update($pathparams['id'], $body);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function delete()
  {
    try {
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->userService->delete($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
