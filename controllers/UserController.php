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

      if (array_key_exists('filter', $queryparams)) {
        Validator::with($queryparams, 'filter')->isString();

        $data = $this->userService->getAllFilter($queryparams['filter']);

        return Response::json($data);
      }

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
      $body = Router::$body;

      Validator::with($body, ['name', 'lastname', 'email', 'password', 'state', 'role'])->required();
      Validator::with($body, ['name', 'lastname', 'email', 'role'])->isString();
      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(4);
      Validator::with($body, 'state')->isInteger();

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
      $body = Router::$body;

      Validator::with($body, ['name', 'lastname', 'email', 'password', 'role'])
        ->required();
      Validator::with($body, 'email')->isEmail();
      Validator::with($body, 'password')->minLength(4);
      Validator::with($body, 'state')->required()->isInteger();

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

  public function enable()
  {
    try {
      $pathparams = Router::$pathparams;
      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->userService->updateState($pathparams['id'], 1);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function disable()
  {
    try {
      $pathparams = Router::$pathparams;
      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->userService->updateState($pathparams['id'], 0);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
