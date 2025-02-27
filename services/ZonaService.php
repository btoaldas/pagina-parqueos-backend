<?php

require_once __DIR__ . '/../models/ZoneModel.php';
require_once __DIR__ . '/../utils/HttpError.php';

class ZoneService
{
  private $zoneModel;

  public function __construct()
  {
    $this->zoneModel = new ZoneModel();
  }

  public function getAll($limit, $offset)
  {
    return $this->zoneModel->all($limit, $offset);
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->zoneModel->get($id);

    if ($throws && !$data)
      throw HttpError::NotFound("Zone $id does not exist!");

    return $data;
  }

  public function create($data)
  {
    return $this->zoneModel->create($data);
  }

  public function update($id, $data)
  {
    $this->getOne($id);

    $result = $this->zoneModel->update($id, $data);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return true;
  }

  public function delete($id)
  {
    $this->getOne($id);
    $result = $this->zoneModel->delete($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Delete");

    return true;
  }
}
