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

      Validator::with($queryparams)->limitOffset();

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

      Validator::with($pathparams, 'id')->required()->isInteger();

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

      Validator::with($body, ['name', 'fee'])->required();
      Validator::with($body, 'fee')->isNumb();

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

      Validator::with($body, ['name', 'fee'])->required();
      Validator::with($body, 'fee')->isNumb();
      Validator::with($pathparams, 'id')->required()->isInteger();

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

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->zoneService->delete($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
