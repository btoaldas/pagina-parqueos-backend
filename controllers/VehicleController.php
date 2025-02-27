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

  // Obtener todos los vehículos con paginación
  public function getAll()
  {
    try {
      $queryparams = Router::$queryparams;

      Validator::with($queryparams)->limitOffset();

      $data = $this->vehicleService->getAll($queryparams['limit'], $queryparams['offset']);

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
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::with($body, ['id_user', 'plate', 'brand', 'model', 'year', 'taxable_base'])->required();
      Validator::with($body, ['year', 'id_user'])->isInteger();
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
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::with($pathparams, 'id')->required()->isInteger();
      Validator::with($body, ['id_user', 'plate', 'brand', 'model', 'year', 'taxable_base'])->required();
      Validator::with($body, ['year', 'id_user'])->isInteger();
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
