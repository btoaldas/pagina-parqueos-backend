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

  public function getAll($limit, $offset)
  {
    $values = $this->fineModel->all($limit, $offset);
    $values = array_map(function ($value) {
      $value['ticket'] = json_decode($value['ticket'], true);
      return $value;
    }, $values);
    return $values;
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->fineModel->get($id);

    if ($throws && !$data)
      throw HttpError::NotFound("Fine $id does not exist!");

    $data['ticket'] = json_decode($data['ticket'], true);

    return $data;
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

  public function pay($id)
  {
    $ticket = $this->getOne($id);

    if ($ticket['state'] !== 'pendiente')
      throw HttpError::BadRequest("Can't pay this fine");

    $result = $this->fineModel->pay($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return $result;
  }

  public function cancel($id)
  {
    $fine = $this->getOne($id);

    if ($fine['state'] !== 'pendiente')
      throw HttpError::BadRequest("Can't cancel this fine");

    $result = $this->fineModel->cancel($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return $result;
  }
}
