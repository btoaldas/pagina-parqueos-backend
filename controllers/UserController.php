<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/UserModel.php';

class UserController
{
  private $userModel;

  public function __construct()
  {
    $this->userModel = new UserModel();
  }

  public function createUser($userData)
  {
    return $this->userModel->create($userData);
  }

  public function getUser($userId)
  {
    return $this->userModel->read($userId);
  }

  public function updateUser($userId, $userData)
  {
    return $this->userModel->update($userId, $userData);
  }

  public function deleteUser($userId)
  {
    return $this->userModel->delete($userId);
  }
}
