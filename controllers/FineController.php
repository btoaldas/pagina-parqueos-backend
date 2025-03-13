<?php

namespace App\Controllers;

use App\Services\FineService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class FineController
{
  private $fineService;

  public function __construct()
  {
    $this->fineService = new FineService();
  }

  public function getAll()
  {
    try {
      $queryparams = Router::$queryparams;

      Validator::with($queryparams)->limitOffset();

      $data = $this->fineService->getAll($queryparams['limit'], $queryparams['offset']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getAllByPlate()
  {
    try {
      $pathparams = Router::$pathparams;
      Validator::with($pathparams, 'plate')->required();

      $data = $this->fineService->getAllByPlate($pathparams['plate']);

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

      $data = $this->fineService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }


  public function create()
  {
    try {
      $payload = $GLOBALS['payload'];

      Validator::with($payload, 'id')->required()->isInteger()->toInteger();

      Validator::with($_FILES, 'image')->required();
      Validator::with($_POST, 'json')->required();

      $body = json_decode($_POST['json'], true);

      Validator::with($body, ['id_vehicle', 'amount'])->required();
      Validator::with($body, 'description')->required(true)->isString();
      Validator::with($body, ['id_vehicle'])->isInteger();
      Validator::with($body, 'amount')->isNumb();

      $body['id_employ'] = $payload['id'];

      $image = $_FILES['image'];

      Validator::with($image, ['tmp_name', 'error', 'size', 'type'])->required();
      Validator::with($image, 'size')->isInteger()->max(1024 * 1024 * 2);

      // Usar Fileinfo para verificar el tipo MIME real del archivo
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $fileMimeType = finfo_file($finfo, $image['tmp_name']);

      $image['type'] = $fileMimeType;

      Validator::with($image, 'type')->isIn(['image/jpeg', 'image/png']);

      $response = $this->fineService->create($body, $image);

      Response::json($response);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function pay()
  {
    try {
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->fineService->pay($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function cancel()
  {
    try {
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->fineService->cancel($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
