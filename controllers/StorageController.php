<?php

namespace App\Controllers;

use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class StorageController
{
  public function getFineFile()
  {
    try {
      $params = Router::$pathparams;
      Validator::with($params, 'filename')->required()->isString();

      $path = $_ENV['PATH_STORAGE'] !== '' ? $_ENV['PATH_STORAGE'] : __DIR__ . "/../storage";
      $path = $path . '/fine/' . $params['filename'];

      if (file_exists($path)) {
        Response::image($path);
      } else {
        throw HttpError::NotFound("$path not found");
      }
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
