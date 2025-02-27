<?php

include_once __DIR__ . '/../services/RoleService.php';

include_once __DIR__ . '/../utils/Validator.php';
include_once __DIR__ . '/../utils/Response.php';
include_once __DIR__ . '/../utils/ErrorHandler.php';
include_once __DIR__ . '/../utils/HttpError.php';

class RoleController
{
  private $roleService;

  public function __construct()
  {
    $this->roleService = new RoleService();
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

      $data = $this->roleService->getAll($queryparams['limit'], $queryparams['offset']);

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

      $data = $this->roleService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function create()
  {
    try {
      $body = json_decode(file_get_contents('php://input'), true);
      Validator::validateRequiredFields($body, ['name', 'description']);

      $data = $this->roleService->create($body);

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

      Validator::validateRequiredFields($body, ['name', 'description']);
      Validator::validateRequiredFields($pathparams, ['id']);
      Validator::isInt($pathparams, 'id');

      $data = $this->roleService->update($pathparams['id'], $body);

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

      $data = $this->roleService->delete($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
