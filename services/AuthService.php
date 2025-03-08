<?php

namespace App\Services;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Utils\ErrorHandler;
use App\Utils\HttpError;
use App\Utils\JWT;

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

    $token = JWT::generateToken($user['id'], $user['role']);

    unset($user['password']);

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

  public function updatePassword($id, $password)
  {
    $exits = $this->userModel->getOne($id);
    if (!$exits)
      throw ErrorHandler::handlerError("User does not exists");

    $password = password_hash($password, PASSWORD_BCRYPT);

    $this->userModel->updatePassword($id, $password);
  }

  public function generateCode($id)
  {
    $code = (string)rand(1e+5, 1e+6 - 1);
    $this->userModel->updateCode($id, $code);
    return $code;
  }

  public function validateCode($user, $code)
  {
    if (is_null($user['code']))
      throw HttpError::BadRequest("This User does not have a recover code");

    if ($user['code'] != $code)
      throw HttpError::BadRequest("this is not the code");
  }
}
