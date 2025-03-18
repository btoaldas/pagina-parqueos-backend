<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\FineService;
use App\Services\ReportService;
use App\Services\TicketService;
use App\Services\UserService;
use App\Services\VehicleService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;
use Error;

class ProfileController
{
  private $userService;
  private $ticketService;
  private $fineService;
  private $vehicleService;
  private $reportService;

  public function __construct()
  {
    $this->userService = new UserService();
    $this->ticketService = new TicketService();
    $this->fineService = new FineService();
    $this->vehicleService = new VehicleService();
    $this->reportService = new ReportService();
  }

  public function getProfile()
  {
    try {
      $payload = $GLOBALS['payload'];
      Validator::with($payload, 'id')->required()->isInteger()->toInteger();

      $user = $this->userService->getOne($payload['id']);

      Response::json($user);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function update()
  {
    try {
      $payload = $GLOBALS['payload'];
      Validator::with($payload, ['id', 'role'])->required();
      Validator::with($payload, 'id')->isInteger()->toInteger();
      $body = Router::$body;
      Validator::with($body, ['name', 'lastname'])->required()->isString()->minLength(2);

      $value = $this->userService->updateProfile($payload['id'], $body['name'], $body['lastname']);

      Response::json($value);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function updatePassword()
  {
    try {
      $payload = $GLOBALS['payload'];
      Validator::with($payload, ['id', 'role'])->required();
      Validator::with($payload, 'id')->isInteger()->toInteger();

      $body = Router::$body;
      Validator::with($body, ['password', 'new-password'])->required()->isString()->minLength(4);

      $this->userService->updatePassword($payload['id'], $body['password'], $body['new-password']);

      Response::json(true);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getTicketsFromUser()
  {
    try {
      $payload = $GLOBALS['payload'];
      $tickets = $this->ticketService->getTicketsFromUser($payload['id']);

      Response::json($tickets);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getFinesFromUser()
  {
    try {
      $payload = $GLOBALS['payload'];
      $tickets = $this->fineService->getFinesFromUser($payload['id']);
      Response::json($tickets);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function getVehiclesFromUser()
  {
    try {
      $payload = $GLOBALS['payload'];
      $vehicles = $this->vehicleService->getAllFromUser($payload['id']);
      Response::json($vehicles);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function downloadPdf()
  {
    try {
      $dompdf = $this->reportService->generatePdf();

      $dompdf->stream("document.pdf", array("Attachment" => false));
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  // extension=iconv
  public function downloadExcel()
  {
    try {
      $params = Router::$pathparams;
      Validator::with($params, 'id')->required()->isInteger()->toInteger();

      $writer = $this->reportService->generateExcelByEmployee($params['id']);

      $path = $_ENV['PATH_STORAGE'] !== '' ? $_ENV['PATH_STORAGE'] : __DIR__ . "/../storage";
      $writer->save($path . '/report.xlsx');

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="report.xlsx"');
      header('Cache-Control: max-age=0');

      $writer->save('php://output');
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
