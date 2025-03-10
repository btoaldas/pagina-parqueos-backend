<?php

namespace App\Models;

use PDO;
use App\Config\Database;
use App\Utils\AesEncryption;

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
    $userData['name'] = AesEncryption::encypt($userData['name']);
    $userData['lastname'] = AesEncryption::encypt($userData['lastname']);
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

    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $values = array_map(function ($value) {
      $value['name'] = AesEncryption::decrypt($value['name']);
      $value['lastname'] = AesEncryption::decrypt($value['lastname']);
      return $value;
    }, $values);
    return $values;
  }

  public function getAllByFilter(string $filter)
  {
    $sql = "SELECT
      u.id,
      u.name,
      u.lastname,
      u.email,
      u.role,
      u.state
    FROM (
      SELECT
        _u.id_usuario AS id,
        _u.nombre AS name,
        _u.apellido AS lastname,
        _u.correo AS email,
        _r.nombre_rol AS role,
        _u.estado AS state,
        LOWER(_u.correo) LIKE CONCAT('%', LOWER(:filter), '%') AS _sort
      FROM usuarios _u
      JOIN roles _r
        ON _u.id_rol = _r.id_rol
      ) u
    ;";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute(['filter' => $filter]);

    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $values = array_map(function ($value) {
      $value['name'] = AesEncryption::decrypt($value['name']);
      $value['lastname'] = AesEncryption::decrypt($value['lastname']);
      return $value;
    }, $values);
    return $values;
  }

  public function getOne($userId, $withPassword = false)
  {
    $sql = "SELECT
      u.id_usuario AS id,
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
    $value = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$value) return null;
    $value['name'] = AesEncryption::decrypt($value['name']);
    $value['lastname'] = AesEncryption::decrypt($value['lastname']);
    return $value;
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
      u.estado AS state,
      u.cdigo_recuperacion AS code
    FROM usuarios u
    JOIN roles r
      ON u.id_rol = r.id_rol
    WHERE u.correo = :email
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['email' => $email]);
    $value = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$value) return null;
    $value['name'] = AesEncryption::decrypt($value['name']);
    $value['lastname'] = AesEncryption::decrypt($value['lastname']);
    return $value;
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
    return $stmt->execute(['id' => $id, 'name' => AesEncryption::encypt($name), 'lastname' => AesEncryption::encypt($lastname)]);
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
    $userData['name'] = AesEncryption::encypt($userData['name']);
    $userData['lastname'] = AesEncryption::encypt($userData['lastname']);
    $userData['id'] = $userId;
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute($userData);
  }

  public function updateState(int $id, int $state)
  {
    $sql = "UPDATE usuarios
    SET
      estado = :state
    WHERE id_usuario = :id
    ";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id, 'state' => $state]);
  }

  public function updatePassword($userId, $password)
  {
    $sql = "UPDATE usuarios
    SET
      contraseña = :password,
      cdigo_recuperacion = NULL
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

  public function updateCode(int $id, $code)
  {
    $sql = "UPDATE usuarios
    SET
      cdigo_recuperacion = :code
    WHERE id_usuario = :id
    ";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id, 'code' => $code]);
  }
}
