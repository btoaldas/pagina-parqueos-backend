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
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
      $writer = $this->reportService->generateExcel();

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
