<?php

namespace App\Services;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Utils\HttpError;

class UserService
{
  private $userModel;
  private $roleModel;

  public function __construct()
  {
    $this->userModel = new UserModel();
    $this->roleModel = new RoleModel();
  }

  public function getAll($limit, $offset)
  {
    return $this->userModel->all($limit, $offset);
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->userModel->getOne($id);

    if ($throws && !$data)
      throw HttpError::NotFound("User $id does not exist!");

    return $data;
  }

  public function create($data)
  {
    $role = $this->roleModel->getByName($data['role']);
    if (!$role)
      throw HttpError::BadRequest("There is not {$data['role']} role");

    unset($data['role']);
    $data['id_role'] = $role['id'];

    $userWithSameEmail = $this->userModel->getUserbyEmail($data['email']);
    if ($userWithSameEmail)
      throw HttpError::BadRequest("User With email {$data['email']} Already exists!");

    $userData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

    return $this->userModel->create($data);
  }

  public function update($id, $data)
  {
    $already = $this->getOne($id);

    $role = $this->roleModel->getByName($data['role']);
    if (!$role)
      throw HttpError::BadRequest("There is not {$data['role']} role");

    unset($data['role']);
    $data['id_role'] = $role['id'];

    if ($already['email'] != $data['email']) {
      $userWithSameEmail = $this->userModel->getUserbyEmail($data['email']);
      if ($userWithSameEmail)
        throw HttpError::BadRequest("User With email {$data['email']} Already exists!");
    }

    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

    $result = $this->userModel->update($id, $data);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return true;
  }

  public function delete($id)
  {
    $this->getOne($id);
    $result = $this->userModel->delete($id);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Delete");

    return true;
  }
}
