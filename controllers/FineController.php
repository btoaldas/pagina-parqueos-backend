<?php

namespace App\Controllers;

use App\Services\FineService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Validator;

class FineController
{
  private $fineService;

  public function __construct()
  {
    $this->fineService = new FineService();
  }

  public function create()
  {
    try {
      Validator::with($_FILES, 'image')->required();
      Validator::with($_POST, 'json')->required();

      $body = json_decode($_POST['json'], true);

      Validator::with($body, ['id_ticket', 'amount', 'state'])->required();
      Validator::with($body, 'description')->required(true)->isString();
      Validator::with($body, 'id_ticket')->isInteger();
      Validator::with($body, 'amount')->isNumb();

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
}
