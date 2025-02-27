<?php

require_once __DIR__ . '/../config/db.php';

class UserModel
{
  private PDO $conn;

  public function __construct()
  {
    global $conn;
    $this->conn = $conn;
  }

  public function create($userData)
  {
    $sql = "INSERT INTO usuarios (nombre, apellido, correo, contraseña, id_rol, estado) VALUES (:name, :lastname, :email, :password, :id_role, :state)";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute($userData);
  }

  public function getOne($userId)
  {
    $sql = "SELECT nombre AS name, apellido AS lastname, correo as email, contraseña as password, id_rol AS id_role, estado AS state FROM usuarios WHERE id_usuario = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getUserbyEmail($email)
  {
    $sql = "SELECT id_usuario AS id, nombre AS name, apellido AS lastname, correo as email, contraseña as password, id_rol AS id_role, estado AS state FROM usuarios WHERE correo = :email";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function update($userId, $userData)
  {
    $sql = "UPDATE usuarios SET nombre = :name, apellido = :lastname, estado = :state, correo = :email, id_rol = :role WHERE id_usuario = :id";
    $stmt = $this->conn->prepare($sql);
    $userData['id'] = $userId;
    return $stmt->execute($userData);
  }

  public function delete($userId)
  {
    $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $userId]);
  }
}
