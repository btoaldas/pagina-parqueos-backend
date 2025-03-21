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
      $data = $this->zoneService->getAll();

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

      $data = $this->zoneService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function create()
  {
    try {
      $body = Router::$body;

      Validator::with($body, ['name', 'fee', 'max_time', 'address', 'description'])->required();
      Validator::with($body, 'fee')->isNumb();
      Validator::with($body, 'max_time')->isInteger();

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
      $body = Router::$body;

      Validator::with($body, ['name', 'fee', 'max_time', 'address', 'description'])->required();
      Validator::with($body, 'fee')->isNumb();
      Validator::with($body, 'max_time')->isInteger();
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
