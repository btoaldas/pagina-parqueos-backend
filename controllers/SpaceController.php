<?php

namespace App\Controllers;

use App\Services\SpaceService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class SpaceController
{
  private $spaceService;

  public function __construct()
  {
    $this->spaceService = new SpaceService();
  }

  public function getAll()
  {
    try {
      $data = $this->spaceService->getAll();

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getAllByZone()
  {
    try {
      $pathparams = Router::$pathparams;
      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->spaceService->getAllByZone($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function available()
  {
    try {
      $data = $this->spaceService->available();
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

      $data = $this->spaceService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function create()
  {
    try {
      $body = Router::$body;

      Validator::with($body, ['state', 'type', 'id_zone', 'latitude', 'longitude'])->required();
      Validator::with($body, 'id_zone')->isInteger();
      Validator::with($body, ['latitude', 'longitude'])->isNumb();
      Validator::with($body, 'state')->isIn(["disponible", "ocupado", "mantenimiento"]);

      $data = $this->spaceService->create($body);

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

      Validator::with($body, ['state', 'type', 'id_zone', 'latitude', 'longitude'])->required();
      Validator::with($body, 'id_zone')->isInteger();
      Validator::with($body, ['latitude', 'longitude'])->isNumb();
      Validator::with($body, 'state')->isIn(["disponible", "ocupado", "mantenimiento"]);
      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->spaceService->update($pathparams['id'], $body);

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

      $data = $this->spaceService->delete($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
