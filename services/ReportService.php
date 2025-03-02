<?php

namespace App\Services;

use App\Models\ReportModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Utils\HttpError;
use App\Utils\JWT;

class ReportService
{
  private $reportModel;

  public function __construct()
  {
    $this->reportModel = new ReportModel();
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
}
