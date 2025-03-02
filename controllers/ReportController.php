<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\ReportService;
use App\Services\UserService;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\Response;
use App\Utils\Router;
use App\Utils\Validator;

class ReportController
{
  private $reportService;

  public function __construct()
  {
    $this->reportService = new ReportService();
  }

  public function main()
  {
    try {
      $value = $this->reportService->mainInformation();
      Response::json($value);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }

  public function report()
  {
    try {
      $value = $this->reportService->statsInformation();
      Response::json($value);
    } catch (HttpError $e) {
      ErrorHandler::handlerError($e->getMessage(), $e->getStatusCode());
    }
  }
}
