<?php

namespace App\Services;

use App\Models\ReportModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Utils\AesEncryption;
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
    $data = $this->reportModel->getReportTicktes();
    foreach ($data as $key => $value) {
      if ($value['user_name']) {
        $data[$key]['user_name'] = AesEncryption::decrypt($value['user_name']);
        $data[$key]['user_lastname'] = AesEncryption::decrypt($value['user_lastname']);
      }
      $data[$key]['employee_name'] = AesEncryption::decrypt($value['employee_name']);
      $data[$key]['employee_lastname'] = AesEncryption::decrypt($value['employee_lastname']);
    }

    // Array ( [0] => id [1] => entry_date [2] => exit_date [3] => amount [4] => state [5] => user_name [6] => user_lastname [7] => user_email [8] => employee_id [9] => employee_name [10] => employee_lastname [11] => space_id [12] => space_state [13] => space_type [14] => zone_id [15] => zone_name [16] => zone_fee [17] => zone_max_time [18] => zone_address [19] => zone_description [20] => vehicle_id [21] => vehicle_plate [22] => vehicle_brand [23] => vehicle_year [24] => vehicle_taxable_base )

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Id');
    $sheet->setCellValue('B1', 'Fecha de entrada');
    $sheet->setCellValue('C1', 'Fecha de salida');
    $sheet->setCellValue('D1', 'Monto');
    $sheet->setCellValue('E1', 'Estado');
    $sheet->setCellValue('F1', 'Nombre de usuario');
    $sheet->setCellValue('G1', 'Apellido de usuario');
    $sheet->setCellValue('H1', 'Correo de usuario');
    $sheet->setCellValue('I1', 'Id de empleado');
    $sheet->setCellValue('J1', 'Nombre de empleado');
    $sheet->setCellValue('K1', 'Apellido de empleado');
    $sheet->setCellValue('L1', 'Id de espacio');
    $sheet->setCellValue('M1', 'Estado de espacio');
    $sheet->setCellValue('N1', 'Tipo de espacio');
    $sheet->setCellValue('O1', 'Id de zona');
    $sheet->setCellValue('P1', 'Nombre de zona');
    $sheet->setCellValue('Q1', 'Tarifa de zona');
    $sheet->setCellValue('R1', 'Tiempo maximo de zona');
    $sheet->setCellValue('S1', 'Direccion de zona');
    $sheet->setCellValue('T1', 'Descripcion de zona');
    $sheet->setCellValue('U1', 'Id de vehiculo');
    $sheet->setCellValue('V1', 'Placa de vehiculo');
    $sheet->setCellValue('W1', 'Marca de vehiculo');
    $sheet->setCellValue('X1', 'Año de vehiculo');
    $sheet->setCellValue('Y1', 'Base imponible de vehiculo');

    for ($i = 0; $i < count($data); $i++) {
      $id = $data[$i]['id'] + 1;

      $sheet->setCellValue("A$id", $data[$i]['id']);
      $sheet->setCellValue("B$id", $data[$i]['entry_date']);
      $sheet->setCellValue("C$id", $data[$i]['exit_date']);
      $sheet->setCellValue("D$id", $data[$i]['amount']);
      $sheet->setCellValue("E$id", $data[$i]['state']);
      $sheet->setCellValue("F$id", $data[$i]['user_name']);
      $sheet->setCellValue("G$id", $data[$i]['user_lastname']);
      $sheet->setCellValue("H$id", $data[$i]['user_email']);
      $sheet->setCellValue("I$id", $data[$i]['employee_id']);
      $sheet->setCellValue("J$id", $data[$i]['employee_name']);
      $sheet->setCellValue("K$id", $data[$i]['employee_lastname']);
      $sheet->setCellValue("L$id", $data[$i]['space_id']);
      $sheet->setCellValue("M$id", $data[$i]['space_state']);
      $sheet->setCellValue("N$id", $data[$i]['space_type']);
      $sheet->setCellValue("O$id", $data[$i]['zone_id']);
      $sheet->setCellValue("P$id", $data[$i]['zone_name']);
      $sheet->setCellValue("Q$id", $data[$i]['zone_fee']);
      $sheet->setCellValue("R$id", $data[$i]['zone_max_time']);
      $sheet->setCellValue("S$id", $data[$i]['zone_address']);
      $sheet->setCellValue("T$id", $data[$i]['zone_description']);
      $sheet->setCellValue("U$id", $data[$i]['vehicle_id']);
      $sheet->setCellValue("V$id", $data[$i]['vehicle_plate']);
      $sheet->setCellValue("W$id", $data[$i]['vehicle_brand']);
      $sheet->setCellValue("X$id", $data[$i]['vehicle_year']);
      $sheet->setCellValue("Y$id", $data[$i]['vehicle_taxable_base']);
    }

    $writer = new Xlsx($spreadsheet);

    return $writer;
  }

  public function generateExcelByEmployee($id)
  {
    $data = $this->reportModel->getReportTicktesByUser($id);
    foreach ($data as $key => $value) {
      if ($value['user_name']) {
        $data[$key]['user_name'] = AesEncryption::decrypt($value['user_name']);
        $data[$key]['user_lastname'] = AesEncryption::decrypt($value['user_lastname']);
      }
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Id');
    $sheet->setCellValue('B1', 'Fecha de entrada');
    $sheet->setCellValue('C1', 'Fecha de salida');
    $sheet->setCellValue('D1', 'Monto');
    $sheet->setCellValue('E1', 'Estado');
    $sheet->setCellValue('F1', 'Id de espacio');
    $sheet->setCellValue('G1', 'Estado de espacio');
    $sheet->setCellValue('H1', 'Tipo de espacio');
    $sheet->setCellValue('I1', 'Id de zona');
    $sheet->setCellValue('J1', 'Nombre de zona');
    $sheet->setCellValue('K1', 'Tarifa de zona');
    $sheet->setCellValue('L1', 'Tiempo maximo de zona');
    $sheet->setCellValue('M1', 'Direccion de zona');
    $sheet->setCellValue('N1', 'Descripcion de zona');
    $sheet->setCellValue('O1', 'Id de vehiculo');
    $sheet->setCellValue('P1', 'Placa de vehiculo');
    $sheet->setCellValue('Q1', 'Marca de vehiculo');
    $sheet->setCellValue('R1', 'Año de vehiculo');
    $sheet->setCellValue('S1', 'Base imponible de vehiculo');
    $sheet->setCellValue('T1', 'Nombre de usuario');
    $sheet->setCellValue('U1', 'Apellido de usuario');
    $sheet->setCellValue('V1', 'Correo de usuario');

    for ($i = 0; $i < count($data); $i++) {
      $id = $data[$i]['id'] + 1;

      $sheet->setCellValue("A$id", $data[$i]['id']);
      $sheet->setCellValue("B$id", $data[$i]['entry_date']);
      $sheet->setCellValue("C$id", $data[$i]['exit_date']);
      $sheet->setCellValue("D$id", $data[$i]['amount']);
      $sheet->setCellValue("E$id", $data[$i]['state']);
      $sheet->setCellValue("F$id", $data[$i]['space_id']);
      $sheet->setCellValue("G$id", $data[$i]['space_state']);
      $sheet->setCellValue("H$id", $data[$i]['space_type']);
      $sheet->setCellValue("I$id", $data[$i]['zone_id']);
      $sheet->setCellValue("J$id", $data[$i]['zone_name']);
      $sheet->setCellValue("K$id", $data[$i]['zone_fee']);
      $sheet->setCellValue("L$id", $data[$i]['zone_max_time']);
      $sheet->setCellValue("M$id", $data[$i]['zone_address']);
      $sheet->setCellValue("N$id", $data[$i]['zone_description']);
      $sheet->setCellValue("O$id", $data[$i]['vehicle_id']);
      $sheet->setCellValue("P$id", $data[$i]['vehicle_plate']);
      $sheet->setCellValue("Q$id", $data[$i]['vehicle_brand']);
      $sheet->setCellValue("R$id", $data[$i]['vehicle_year']);
      $sheet->setCellValue("S$id", $data[$i]['vehicle_taxable_base']);
      $sheet->setCellValue("T$id", $data[$i]['user_name']);
      $sheet->setCellValue("U$id", $data[$i]['user_lastname']);
      $sheet->setCellValue("V$id", $data[$i]['user_email']);
    }

    $writer = new Xlsx($spreadsheet);

    return $writer;
  }
}
