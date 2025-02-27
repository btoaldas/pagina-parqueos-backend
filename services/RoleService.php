<?php

require_once __DIR__ . '/../models/RoleModel.php';
require_once __DIR__ . '/../utils/HttpError.php';

class RoleService
{
  private $roleModel;

  public function __construct()
  {
    $this->roleModel = new RoleModel();
  }

  public function getAll($limit, $offset)
  {
    return $this->roleModel->all($limit, $offset);
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->roleModel->get($id);

    if ($throws && !$data)
      throw HttpError::NotFound("Role $id does not exiest!");

    return $data;
  }

  public function create($data)
  {
    return $this->roleModel->create($data);
  }

  public function update($id, $data)
  {
    $this->getOne($id);

    $result = $this->roleModel->update($id, $data);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return true;
  }

  public function delete($id)
  {
    $this->getOne($id);
    $result = $this->roleModel->delete($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Delete");

    return true;
  }
}
