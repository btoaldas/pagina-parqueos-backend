<?php

namespace App\Services;

use App\Models\ZoneModel;
use App\Utils\HttpError;

class ZoneService
{
  private $zoneModel;

  public function __construct()
  {
    $this->zoneModel = new ZoneModel();
  }

  public function getAll($limit, $offset)
  {
    $values = $this->zoneModel->all($limit, $offset);
    $values = array_map(function ($value) {
      $value['fee'] = (float)$value['fee'];
      return $value;
    }, $values);
    return $values;
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->zoneModel->get($id);

    if ($throws && !$data)
      throw HttpError::NotFound("Zone $id does not exist!");

    $data['fee'] = (float)$data['fee'];

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
