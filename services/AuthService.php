<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/RoleModel.php';
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/HttpError.php';

class AuthService
{
  private $userModel;
  private $roleModel;

  public function __construct()
  {
    $this->userModel = new UserModel();
    $this->roleModel = new RoleModel();
  }

  public function login($email, $password)
  {
    $user = $this->userModel->getUserbyEmail($email);
    if (!$user || !password_verify($password, $user['password']))
      throw HttpError::BadRequest("User or password incorrect");

    $role = $this->roleModel->get($user['id_role']);

    $token = JWT::generateToken($user['id'], $role['name']);

    unset($user['password']);
    unset($user['id']);
    unset($user['id_role']);

    $user['role'] = $role['name'];

    return ['token' => $token, 'user' => $user];
  }

  public function register($userData)
  {
    $exits = $this->userModel->getUserbyEmail($userData["email"]);

    if (!!$exits)
      throw HttpError::BadRequest("This email already exists!");

    $role = $this->roleModel->getByName("cliente");
    if (!$role)
      throw HttpError::InternalServer("There is not client role");

    $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
    $userData['id_role'] = $role['id'];
    $userData['state'] = true;
    return $this->userModel->create($userData);
  }
}
