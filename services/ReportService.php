<?php

namespace App\Services;

use App\Models\ReportModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Utils\HttpError;
use App\Utils\JWT;
use App\Utils\Template;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportService
{
  private $reportModel;
  private $userService;

  public function __construct()
  {
    $this->reportModel = new ReportModel();
    $this->userService = new UserService();
  }

  public function mainInformation()
  {
    $activeUsers = $this->reportModel->activeUsers();
    $incomeToday = $this->reportModel->earnToday();
    $spacesTaken = $this->reportModel->spacesTaken();
    $fees = $this->reportModel->fees();

    return [
      "active_users" => $activeUsers,
      "income_today" => $incomeToday,
      "spaces_taken" => $spacesTaken,
      "fees" => $fees,
    ];
  }

  public function statsInformation()
  {
    $incomeByMonth = $this->reportModel->earnByMonth();
    $eachSpaceTaken = $this->reportModel->eachSpaceTaken();
    $usersByRol = $this->reportModel->usersByRol();

    return [
      "income_month" => $incomeByMonth,
      "each_space_taken" => $eachSpaceTaken,
      "users_rol" => $usersByRol,
    ];
  }

  public function generatePdf()
  {
    $dompdf = new Dompdf();

    $data = $this->mainInformation();

    $html = Template::htmlMainReport($data);

    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf;
  }

  public function generateExcel()
  {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Id');
    $sheet->setCellValue('B1', 'Nombre');
    $sheet->setCellValue('C1', 'Apellido');
    $sheet->setCellValue('D1', 'Correo');
    $sheet->setCellValue('E1', 'Rol');
    $sheet->setCellValue('F1', 'Estado');

    $users = $this->userService->getAll(1000, 0);

    for ($i = 0; $i < count($users); $i++) {
      $id = $users[$i]['id'] + 1;

      $sheet->setCellValue("A$id", $users[$i]['id']);
      $sheet->setCellValue("B$id", $users[$i]['name']);
      $sheet->setCellValue("C$id", $users[$i]['lastname']);
      $sheet->setCellValue("D$id", $users[$i]['email']);
      $sheet->setCellValue("E$id", $users[$i]['role']);
      $sheet->setCellValue("F$id", $users[$i]['state']);
    }

    $writer = new Xlsx($spreadsheet);

    return $writer;
  }
}
