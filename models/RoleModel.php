<?php

namespace App\Models;

use PDO;
use App\Config\Database;

class RoleModel
{
  private PDO $conn;

  public function __construct()
  {
    $this->conn = Database::getConnection();
  }

  public function create($data)
  {
    $sql = "INSERT INTO roles
      (nombre_rol, descripcion)
    VALUES
      (:name, :description)
    ";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute($data);
  }

  public function get($id)
  {
    $sql = "SELECT
      nombre_rol AS name,
      descripcion AS description
    FROM roles
    WHERE id_rol = :id
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getByName($name)
  {
    $sql = "SELECT
      id_rol AS id
    FROM roles
    WHERE nombre_rol = :name
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute(['name' => $name]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function all($limit = 10, $offset = 0)
  {
    $sql = "SELECT
      id_rol AS id,
      nombre_rol AS name,
      descripcion AS description
    FROM roles
    LIMIT :limit
    OFFSET :offset
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function update($id, $data)
  {
    $sql = "UPDATE roles
    SET
      nombre_rol = :name,
      descripcion = :description
    WHERE id_rol = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $data['id'] = $id;

    return $stmt->execute($data);
  }

  public function delete($id)
  {
    $sql = "DELETE FROM roles WHERE id_rol = :id";
    $stmt = $this->conn->prepare($sql);

    return $stmt->execute(['id' => $id]);
  }
}
