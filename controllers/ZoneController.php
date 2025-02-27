<?php

include_once __DIR__ . '/../services/ZonaService.php';

include_once __DIR__ . '/../utils/Validator.php';
include_once __DIR__ . '/../utils/Response.php';
include_once __DIR__ . '/../utils/ErrorHandler.php';
include_once __DIR__ . '/../utils/HttpError.php';

class ZoneController
{
  private $zoneService;

  public function __construct()
  {
    $this->zoneService = new ZoneService();
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

      $data = $this->zoneService->getAll($queryparams['limit'], $queryparams['offset']);

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

      $data = $this->zoneService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function create()
  {
    try {
      $body = json_decode(file_get_contents('php://input'), true);
      Validator::validateRequiredFields($body, ['name', 'fee']);
      Validator::isNumber($body, 'fee');

      $data = $this->zoneService->create($body);

      Response::json($data, "Created", 201);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function update()
  {
    try {
      global $pathparams;
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::validateRequiredFields($body, ['name', 'fee']);
      Validator::isNumber($body, 'fee');
      Validator::validateRequiredFields($pathparams, ['id']);
      Validator::isInt($pathparams, 'id');

      $data = $this->zoneService->update($pathparams['id'], $body);

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

      $data = $this->zoneService->delete($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
