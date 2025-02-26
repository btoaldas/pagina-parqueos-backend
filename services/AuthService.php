<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/HttpError.php';

class AuthService
{
  private $userModel;

  public function __construct()
  {
    $this->userModel = new UserModel();
  }

  public function login($email, $password)
  {
    $user = $this->userModel->getUserbyEmail($email);
    if (!$user || !password_verify($password, $user['contraseña']))
      throw HttpError::BadRequest("User or password incorrect");

    $token = JWT::generateToken($user['id_usuario'], $user['id_rol']);

    unset($user['contraseña']);
    unset($user['id_usuario']);

    return ['token' => $token, 'user' => $user];
  }

  public function register($userData)
  {
    $exits = $this->userModel->getUserbyEmail($userData["email"]);

    if (!!$exits)
      throw HttpError::BadRequest("This email already exists!");

    $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
    $userData['id_role'] = 1;
    $userData['state'] = true;
    return $this->userModel->create($userData);
  }
}
