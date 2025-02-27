<?php

include_once __DIR__ . '/../services/UserService.php';

include_once __DIR__ . '/../utils/Validator.php';
include_once __DIR__ . '/../utils/Response.php';
include_once __DIR__ . '/../utils/ErrorHandler.php';
include_once __DIR__ . '/../utils/HttpError.php';

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
      global $queryparams;

      if (empty($queryparams['limit']))
        $queryparams['limit'] = '10';
      if (empty($queryparams['offset']))
        $queryparams['offset'] = '0';

      Validator::isInt($queryparams, 'limit');
      Validator::isInt($queryparams, 'offset');

      $data = $this->userService->getAll($queryparams['limit'], $queryparams['offset']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getOne()
  {
    try {
      global $pathparams;

      Validator::validateRequiredFields($pathparams, ['id']);
      Validator::isInt($pathparams, 'id');

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
      Validator::validateRequiredFields($body, ['name', 'lastname', 'email', 'password', 'state', 'role']);
      Validator::validateEmail($body['email']);
      Validator::validatePassword($body['password']);

      $data = $this->userService->create($body);

      Response::json($data, "Success", 201);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function update()
  {
    try {
      global $pathparams;
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::validateRequiredFields($body, ['name', 'lastname', 'email', 'password', 'state', 'role']);
      Validator::validateEmail($body['email']);
      Validator::validatePassword($body['password']);
      Validator::validateRequiredFields($pathparams, ['id']);
      Validator::isInt($pathparams, 'id');

      $data = $this->userService->update($pathparams['id'], $body);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function delete()
  {
    try {
      global $pathparams;

      Validator::validateRequiredFields($pathparams, ['id']);
      Validator::isInt($pathparams, 'id');

      $data = $this->userService->delete($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
