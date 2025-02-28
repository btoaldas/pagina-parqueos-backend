<?php

namespace App\Services;

use App\Models\SpaceModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use App\Models\VehicleModel;
use App\Utils\HttpError;
use DateTime;

class TicketService
{
  private $spaceModel;
  private $userModel;
  private $ticketModel;
  private $vehiculeModel;

  public function __construct()
  {
    $this->spaceModel = new SpaceModel();
    $this->userModel = new UserModel();
    $this->ticketModel = new TicketModel();
    $this->vehiculeModel = new VehicleModel();
  }

  public function getAll($limit, $offset)
  {
    $values = $this->ticketModel->all($limit, $offset);
    $values = array_map(function ($value) {
      $value['space'] = json_decode($value['space'], true);
      $value['user'] = json_decode($value['user'], true);
      return $value;
    }, $values);
    return $values;
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->ticketModel->get($id);

    if ($throws && !$data)
      throw HttpError::NotFound("Ticket $id does not exist!");

    $data['space'] = json_decode($data['space'], true);
    $data['user'] = json_decode($data['user'], true);
    $data['zone'] = json_decode($data['zone'], true);

    return $data;
  }

  public function create($id_space, $plate)
  {
    $now = (new DateTime())->format('Y-m-d H:i:s');

    $vehicle = $this->vehiculeModel->getByPlate($plate);
    if (!$vehicle)
      throw HttpError::BadRequest("There is not vehicle with plate $plate");
    $vehicle['user'] = json_decode($vehicle['user'], true);
    $id_user = $vehicle['user']['id'];

    $user = $this->userModel->getOne($id_user);
    if (!$user)
      throw HttpError::BadRequest("There is not user $id_user");

    $space = $this->spaceModel->get($id_space);
    if (!$space)
      throw HttpError::BadRequest("There is not space $id_space");

    return $this->ticketModel->create([
      "id_user" => $id_user,
      "id_space" => $id_space,
      "plate" => $vehicle['plate'],
      "entry_date" => $now,
      "state" => "activo",
    ]);
  }

  public function complete($id)
  {
    $ticket = $this->getOne($id);

    if ($ticket['exit_date'])
      throw HttpError::BadRequest("ticket all ready completed");

    if ($ticket['state'] !== 'activo')
      throw HttpError::BadRequest("Can't complete this ticket");

    $entry_time = new DateTime($ticket['entry_date']);
    $now = new DateTime();
    $interval = $now->diff($entry_time);
    $hours = $interval->days * 24 + $interval->h + $interval->i / 60;

    $amount = $hours * $ticket['zone']['fee'];

    $result = $this->ticketModel->complete($id, [
      "exit_date" => $now->format('Y-m-d H:i:s'),
      "amount" => $amount,
      "state" => "finalizado"
    ]);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return $result;
  }

  public function cancel($id)
  {
    $ticket = $this->getOne($id);

    if ($ticket['state'] !== 'activo')
      throw HttpError::BadRequest("Can't cancel this ticket");

    $result = $this->ticketModel->cancel($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return $result;
  }

  public function update($id, $data)
  {
    $ticket = $this->getOne($id);

    if ($ticket['space']['id'] != $data['id_space']) {
      $space = $this->spaceModel->get($data['id_space']);
      if (!$space)
        throw HttpError::BadRequest("There is not space {$data['id_space']}");
    }

    if ($ticket['user']['id'] != $data['id_user']) {
      $user = $this->userModel->getOne($data['id_user']);
      if (!$user)
        throw HttpError::BadRequest("There is not user {$data['id_user']}");
    }

    $result = $this->spaceModel->update($id, $data);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return true;
  }

  public function delete($id)
  {
    $this->getOne($id);
    $result = $this->ticketModel->delete($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Delete");

    return true;
  }
}
