<?php

namespace App\Models;

use PDO;
use App\Config\Database;

class ZoneModel
{
  private PDO $conn;

  public function __construct()
  {
    $this->conn = Database::getConnection();
  }

  public function create($data)
  {
    $sql = "INSERT INTO zonas
      (nombre, tarifa, tiempo_maximo)
    VALUES
      (:name, :fee, :max_time)
    ";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute($data);
  }

  public function all($limit = 10, $offset = 0)
  {
    $sql = "SELECT
      id_zona AS id,
      nombre AS name,
      tarifa AS fee,
      tiempo_maximo AS max_time
    FROM zonas
    LIMIT :limit
    OFFSET :offset
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get($id)
  {
    $sql = "SELECT
      id_zona AS id,
      nombre AS name,
      tarifa AS fee,
      tiempo_maximo AS max_time
    FROM zonas
    WHERE id_zona = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getZoneByName($name)
  {
    $sql = "SELECT
      id_zona AS id,
      nombre AS name,
      tarifa AS fee,
      tiempo_maximo AS max_time
    FROM zonas
    WHERE nombre = :name
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['name' => $name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function update($id, $data)
  {
    $sql = "UPDATE zonas
    SET
      nombre = :name,
      tarifa = :fee,
      tiempo_maximo = :max_time
    WHERE id_zona = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $data['id'] = $id;
    return $stmt->execute($data);
  }

  public function delete($id)
  {
    $sql = "DELETE FROM zonas WHERE id_zona = :id";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id]);
  }
}
