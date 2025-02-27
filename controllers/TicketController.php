<?php

namespace App\Controllers;

use App\Services\TicketService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
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
      global $queryparams;

      Validator::with($queryparams)->limitOffset();

      $data = $this->ticketService->getAll($queryparams['limit'], $queryparams['offset']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getOne()
  {
    try {
      global $pathparams;

      Validator::with($pathparams, 'id')->required()->isInteger();

      $data = $this->ticketService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
