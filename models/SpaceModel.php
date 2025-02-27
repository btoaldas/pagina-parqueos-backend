<?php

namespace App\Models;

use PDO;
use App\Config\Database;

class SpaceModel
{
  private PDO $conn;

  public function __construct()
  {
    $this->conn = Database::getConnection();
  }

  public function create($data)
  {
    $sql = "INSERT INTO espacios (id_zona, estado, tipo) VALUES (:id_zone, :state, :type)";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute($data);
  }

  public function all($limit = 10, $offset = 0)
  {
    $sql = "SELECT
      e.id_espacio AS id,
      e.estado AS state,
      e.tipo AS type,
      JSON_OBJECT(
        'id', z.id_zona,
        'name',z.nombre,
        'fee', z.tarifa
      ) as zone
    FROM espacios e
    JOIN zonas z
      ON e.id_zona = z.id_zona
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
      e.estado AS state,
      e.tipo AS type,
      JSON_OBJECT(
        'id', z.id_zona,
        'name',z.nombre,
        'fee', z.tarifa
      ) as zone
    FROM espacios e
    JOIN zonas z
      ON e.id_zona = z.id_zona
    WHERE e.id_espacio = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    $value = $stmt->fetch(PDO::FETCH_ASSOC);
    return $value;
  }

  public function update($id, $data)
  {
    $sql = "UPDATE espacios
    SET
      estado = :state,
      tipo = :type,
      id_zona = :id_zone
    WHERE
      id_espacio = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $data['id'] = $id;
    return $stmt->execute($data);
  }

  public function delete($id)
  {
    $sql = "DELETE FROM espacios WHERE id_espacio = :id";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id]);
  }
}
