<?php

namespace App\Models;

use PDO;
use App\Config\Database;

class UserModel
{
  private PDO $conn;

  public function __construct()
  {
    $this->conn = Database::getConnection();
  }

  public function create($userData)
  {
    $sql = "INSERT INTO usuarios
      (nombre, apellido, correo, contraseña, id_rol, estado)
    VALUES
      (:name, :lastname, :email, :password, :id_role, :state)
    ";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute($userData);
  }

  public function all($limit = 10, $offset = 0)
  {
    $sql = "SELECT
      u.id_usuario AS id,
      u.nombre AS name,
      u.apellido AS lastname,
      u.correo as email,
      r.nombre_rol AS role,
      u.estado AS state
    FROM usuarios u
    JOIN roles r
      ON u.id_rol = r.id_rol
    LIMIT :limit
    OFFSET :offset
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getOne($userId, $withPassword = false)
  {
    $sql = "SELECT
      u.nombre AS name,
      u.apellido AS lastname,
      u.correo as email,
      r.nombre_rol AS role," .
      ($withPassword ? 'u.contraseña AS password,' : '') .
      "u.estado AS state
    FROM usuarios u
    JOIN roles r
      ON u.id_rol = r.id_rol
    WHERE u.id_usuario = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getUserbyEmail($email)
  {
    $sql = "SELECT
      u.id_usuario AS id,
      u.nombre AS name,
      u.apellido AS lastname,
      u.correo as email,
      u.contraseña as password,
      r.nombre_rol AS role,
      u.estado AS state
    FROM usuarios u
    JOIN roles r
      ON u.id_rol = r.id_rol
    WHERE u.correo = :email
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function profileUpdate(int $id, string $name, string $lastname)
  {
    $sql = "UPDATE usuarios
    SET
      nombre = :name,
      apellido = :lastname
    WHERE id_usuario = :id
    ";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id, 'name' => $name, 'lastname' => $lastname]);
  }

  public function update($userId, $userData)
  {
    $sql = "UPDATE usuarios
    SET
      nombre = :name,
      apellido = :lastname,
      estado = :state,
      correo = :email,
      contraseña = :password,
      id_rol = :id_role
    WHERE id_usuario = :id
    ";
    $stmt = $this->conn->prepare($sql);
    $userData['id'] = $userId;
    return $stmt->execute($userData);
  }

  public function updatePassword($userId, $password)
  {
    $sql = "UPDATE usuarios
    SET
      contraseña = :password
    WHERE id_usuario = :id
    ";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $userId, 'password' => $password]);
  }

  public function delete($userId)
  {
    $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $userId]);
  }
}
