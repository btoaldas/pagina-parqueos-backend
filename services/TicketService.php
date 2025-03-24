<?php

namespace App\Services;

use App\Models\SpaceModel;
use App\Models\TicketModel;
use App\Models\UserModel;
use App\Models\VehicleModel;
use App\Utils\AesEncryption;
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

  public function getAll()
  {
    $values = $this->ticketModel->all();
    $values = array_map(function ($value) {
      $value['space'] = json_decode($value['space'], true);
      $value['zone'] = json_decode($value['zone'], true);
      $value['vehicle'] = json_decode($value['vehicle'], true);

      if (is_null($value['user'])) return $value;
      $value['user'] = json_decode($value['user'], true);
      $value['user']['name'] = AesEncryption::decrypt($value['user']['name']);
      $value['user']['lastname'] = AesEncryption::decrypt($value['user']['lastname']);
      return $value;
    }, $values);
    return $values;
  }

  public function getAllByPlate(string $plate)
  {
    $values = $this->ticketModel->allByPlate($plate);
    $values = array_map(function ($value) {
      $value['space'] = json_decode($value['space'], true);
      $value['zone'] = json_decode($value['zone'], true);
      $value['vehicle'] = json_decode($value['vehicle'], true);

      if (is_null($value['user'])) return $value;
      $value['user'] = json_decode($value['user'], true);
      $value['user']['name'] = AesEncryption::decrypt($value['user']['name']);
      $value['user']['lastname'] = AesEncryption::decrypt($value['user']['lastname']);
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
    $data['zone'] = json_decode($data['zone'], true);
    $data['vehicle'] = json_decode($data['vehicle'], true);

    if (is_null($data['user'])) return $data;
    $data['user'] = json_decode($data['user'], true);
    $data['user']['name'] = AesEncryption::decrypt($data['user']['name']);
    $data['user']['lastname'] = AesEncryption::decrypt($data['user']['lastname']);
    return $data;
  }

  public function create($id_vehicle, $id_space, $id_employ)
  {
    $now = (new DateTime())->format('Y-m-d H:i:s');

    $vehicle = $this->vehiculeModel->get($id_vehicle);

    if (!$vehicle)
      throw HttpError::BadRequest("There is not vehicle with id $id_vehicle");
    $vehicle['user'] = !is_null($vehicle['user']) ? json_decode($vehicle['user'], true) : null;

    $employ = $this->userModel->getOne($id_employ);
    if (!$employ)
      throw HttpError::BadRequest("There is not user $id_employ");

    $space = $this->spaceModel->get($id_space);
    if (!$space)
      throw HttpError::BadRequest("There is not space $id_space");

    if ($space['state'] !== 'disponible')
      throw HttpError::BadRequest("This space is not free");

    $this->spaceModel->setState($id_space, "ocupado");

    return $this->ticketModel->create([
      "id_vehicle" => $id_vehicle,
      "id_space" => $id_space,
      "entry_date" => $now,
      "state" => "activo",
      "id_employ" => $id_employ,
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

    $this->spaceModel->setState($ticket['space']['id'], 'disponible');

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

    $this->spaceModel->setState($ticket['space']['id'], 'disponible');

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

  public function getTicketsFromUser(int $id)
  {
    $values = $this->ticketModel->getTicketsFromUser($id);

    $values = array_map(function ($value) {
      $value['space'] = json_decode($value['space'], true);
      $value['zone'] = json_decode($value['zone'], true);
      $value['vehicle'] = json_decode($value['vehicle'], true);
      return $value;
    }, $values);

    return $values;
  }
}
