<?php

namespace App\Controllers;

use App\Services\VehicleService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class VehicleController
{
  private $vehicleService;

  public function __construct()
  {
    $this->vehicleService = new VehicleService();
  }

  public function getByUser()
  {
    try {
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->vehicleService->getByUser($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getWithNoUSer()
  {
    try {
      $data = $this->vehicleService->getWithNoUser();

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  // Read from json body
  public function updateUser()
  {
    try {
      $body = Router::$body;

      Validator::with($body, ['id_vehicle', 'id_user'])->required()->isInteger()->toInteger();

      $data = $this->vehicleService->updateUser($body['id_vehicle'], $body['id_user']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  // Obtener todos los vehículos con paginación
  public function getAll()
  {
    try {
      $data = $this->vehicleService->getAll();

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  // Obtener un vehículo por su ID
  public function getOne()
  {
    try {
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->vehicleService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  // Crear un nuevo vehículo
  public function create()
  {
    try {
      $body = Router::$body;

      Validator::with($body, ['plate', 'brand', 'model', 'year', 'taxable_base'])->required();
      Validator::with($body, 'id_user')->required(true)->isInteger()->toInteger();
      Validator::with($body, 'year')->isInteger();
      Validator::with($body, 'taxable_base')->isNumb();
      Validator::with($body, 'plate')->isString();

      $data = $this->vehicleService->create($body);

      Response::json($data, "Created", 201);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  // Actualizar un vehículo
  public function update()
  {
    try {
      $pathparams = Router::$pathparams;
      $body = Router::$body;

      Validator::with($pathparams, 'id')->required()->isInteger();
      Validator::with($body, ['plate', 'brand', 'model', 'year', 'taxable_base'])->required();
      Validator::with($body, 'id_user')->required(true)->isInteger()->toInteger();
      Validator::with($body, 'year')->isInteger();
      Validator::with($body, 'taxable_base')->isNumb();
      Validator::with($body, 'plate')->isString();

      $data = $this->vehicleService->update($pathparams['id'], $body);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  // Eliminar un vehículo por su ID
  public function delete()
  {
    try {
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->vehicleService->delete($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
