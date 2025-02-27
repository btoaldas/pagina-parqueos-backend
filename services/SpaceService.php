<?php

require_once __DIR__ . '/../models/SpaceModel.php';
require_once __DIR__ . '/../models/ZoneModel.php';
require_once __DIR__ . '/../utils/HttpError.php';

class SpaceService
{
  private $spaceModel;
  private $zoneModel;

  public function __construct()
  {
    $this->spaceModel = new SpaceModel();
    $this->zoneModel = new ZoneModel();
  }

  public function getAll($limit, $offset)
  {
    $values = $this->spaceModel->all($limit, $offset);
    $values = array_map(function ($value) {
      $value['zone'] = json_decode($value['zone'], true);
      return $value;
    }, $values);
    return $values;
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->spaceModel->get($id);

    if ($throws && !$data)
      throw HttpError::NotFound("Space $id does not exist!");

    $data['zone'] = json_decode($data['zone'], true);

    return $data;
  }

  public function create($data)
  {
    $zone = $this->zoneModel->get($data['id_zone']);
    if (!$zone)
      throw HttpError::BadRequest("There is not zone {$data['id_zone']}");

    return $this->spaceModel->create($data);
  }

  public function update($id, $data)
  {
    $this->getOne($id);

    $zone = $this->zoneModel->get($data['id_zone']);
    if (!$zone)
      throw HttpError::BadRequest("There is not zone {$data['id_zone']}");

    $result = $this->spaceModel->update($id, $data);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return true;
  }

  public function delete($id)
  {
    $this->getOne($id);
    $result = $this->spaceModel->delete($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Delete");

    return true;
  }
}
