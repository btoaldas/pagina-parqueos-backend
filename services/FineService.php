<?php

namespace App\Services;

use App\Models\FineModel;
use App\Models\RoleModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use App\Utils\HttpError;
use App\Utils\JWT;

class FineService
{
  private $fineModel;
  private $ticketModel;

  public function __construct()
  {
    $this->ticketModel = new TicketModel();
    $this->fineModel = new FineModel();
  }

  public function create($data, $image)
  {
    $ticket = $this->ticketModel->get($data['id_ticket']);
    if (!$ticket)
      HttpError::BadRequest("Ticket {$data['id_ticket']} does not exist");

    $data['mime'] = $image['type'];

    $idFine = $this->fineModel->create($data);

    $filename = "{$data['id_ticket']}_$idFine" . ($data['mime'] === 'image/jpeg' ? '.jpg' : '.png');
    move_uploaded_file($image['tmp_name'], __DIR__ . "/../storage/fine/$filename");

    return true;
  }
}
