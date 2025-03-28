<?php

namespace App\Controllers;

use App\Services\TicketService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class TicketController
{
  private $ticketService;

  public function __construct()
  {
    $this->ticketService = new TicketService();
  }

  public function getAll()
  {
    try {
      $data = $this->ticketService->getAll();

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

      $data = $this->ticketService->getAllByPlate($pathparams['plate']);

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

      $data = $this->ticketService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function register()
  {
    try {
      $payload = $GLOBALS['payload'];

      $body = Router::$body;
      Validator::with($body, ['id_space', 'id_vehicle'])->required()->isInteger()->toInteger();

      $data = $this->ticketService->create($body['id_vehicle'], $body['id_space'], $payload['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function validateOut()
  {
    try {
      $pathparams = Router::$pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->ticketService->complete($pathparams['id']);

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

      $data = $this->ticketService->cancel($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
