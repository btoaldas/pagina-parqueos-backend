<?php

namespace App\Services;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Utils\HttpError;

class UserService
{
  private UserModel $userModel;
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

  public function getAllFilter(string $filter)
  {
    return $this->userModel->getAllByFilter($filter);
  }

  public function getOne($id, $throws = true)
  {
    $data = $this->userModel->getOne($id);

    if ($throws && !$data)
      throw HttpError::NotFound("User $id does not exist!");

    return $data;
  }

  public function getByEmail($email, $throws = true)
  {
    $data = $this->userModel->getUserbyEmail($email);

    if ($throws && !$data)
      throw HttpError::NotFound("User $email with email not exist!");

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

    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

    return $this->userModel->create($data);
  }

  public function updateProfile(int $id, string $name, string $lastname)
  {
    $this->getOne($id);

    $result = $this->userModel->profileUpdate($id, $name, $lastname);

    if (!$result)
      throw HttpError::InternalServer("Server Error On Update");

    return true;
  }

  public function updatePassword($id, $password, $newPassword)
  {
    $user = $this->userModel->getOne($id, true);

    if (!$user || !password_verify($password, $user['password']))
      throw HttpError::BadRequest("User or password incorrect");

    $newPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $this->userModel->updatePassword($id, $newPassword);
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

  public function updateState(int $id, int $state)
  {
    $this->getOne($id);

    $result = $this->userModel->updateState($id, $state);
    if (!$result)
      throw HttpError::InternalServer("Server Error On Delete");

    return true;
  }
}
