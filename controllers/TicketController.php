<?php

include_once __DIR__ . '/../services/TicketService.php';

include_once __DIR__ . '/../utils/Validator.php';
include_once __DIR__ . '/../utils/Response.php';
include_once __DIR__ . '/../utils/ErrorHandler.php';
include_once __DIR__ . '/../utils/HttpError.php';

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

      if (empty($queryparams['limit']))
        $queryparams['limit'] = '10';
      if (empty($queryparams['offset']))
        $queryparams['offset'] = '0';

      Validator::isInt($queryparams, 'limit');
      Validator::isInt($queryparams, 'offset');

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

      Validator::validateRequiredFields($pathparams, ['id']);
      Validator::isInt($pathparams, 'id');

      $data = $this->ticketService->getOne($pathparams['id']);

      Response::json($data);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
