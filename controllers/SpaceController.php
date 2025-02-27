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
      $queryparams = Router::$queryparams;

      Validator::with($queryparams)->limitOffset();

      $data = $this->spaceService->getAll($queryparams['limit'], $queryparams['offset']);

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
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::with($body, ['state', 'type', 'id_zone'])->required();
      Validator::with($body, 'id_zone')->isInteger();

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
      $body = json_decode(file_get_contents('php://input'), true);

      Validator::with($body, ['state', 'type', 'id_zone'])->required();
      Validator::with($body, 'id_zone')->isInteger();
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
