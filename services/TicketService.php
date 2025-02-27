<?php

require_once __DIR__ . '/../models/TicketModel.php';
require_once __DIR__ . '/../models/SpaceModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utils/HttpError.php';

class TicketService
{
  private $spaceModel;
  private $userModel;
  private $ticketModel;

  public function __construct()
  {
    $this->spaceModel = new SpaceModel();
    $this->userModel = new UserModel();
    $this->ticketModel = new TicketModel();
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

    return $data;
  }

  public function create($data)
  {
    $user = $this->userModel->getOne($data['id_user']);
    if (!$user)
      throw HttpError::BadRequest("There is not user {$data['id_zone']}");
    $space = $this->spaceModel->get($data['id_space']);
    if (!$space)
      throw HttpError::BadRequest("There is not space {$data['id_zone']}");

    return $this->ticketModel->create($data);
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
