<?php

namespace App\Controllers;

use App\Services\ZoneService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

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
      $queryparams = Router::$queryparams;

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
      $pathparams = Router::$queryparams;

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
      $pathparams = Router::$pathparams;
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
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->zoneService->delete($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
