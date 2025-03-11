<?php

namespace App\Models;

use PDO;
use App\Config\Database;

class VehicleModel
{
  private PDO $conn;

  public function __construct()
  {
    $this->conn = Database::getConnection();
  }

  // Crear un nuevo vehículo
  public function create($data)
  {
    $sql = "INSERT INTO vehiculos
      (id_usuario, placa, marca, modelo, año, base_imponible)
    VALUES
      (:id_user, :plate, :brand, :model, :year, :taxable_base)
    ";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute($data);
  }

  public function allFromUser(int $id)
  {
    $sql = "SELECT
      v.id_vehiculo AS id,
      v.placa AS plate,
      v.marca AS brand,
      v.modelo AS model,
      v.año AS year,
      v.base_imponible AS taxable_base
    FROM vehiculos v
    WHERE v.id_usuario = :id
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute(['id' => $id]);

    $value = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $value;
  }

  // Obtener todos los vehículos con paginación
  public function all($limit = 10, $offset = 0)
  {
    $sql = "SELECT
      v.id_vehiculo AS id,
      v.placa AS plate,
      v.marca AS brand,
      v.modelo AS model,
      v.año AS year,
      v.base_imponible AS taxable_base,
      JSON_OBJECT(
        'id', u.id_usuario,
        'name', u.nombre,
        'lastname', u.apellido,
        'email', u.correo,
        'state', u.estado
      ) AS user
    FROM vehiculos v
    JOIN usuarios u
      ON v.id_usuario = u.id_usuario
    LIMIT :limit
    OFFSET :offset
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    $value = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $value;
  }

  // Obtener un vehículo por su ID
  public function get($id)
  {
    $sql = "SELECT
      v.id_vehiculo AS id,
      v.placa AS plate,
      v.marca AS brand,
      v.modelo AS model,
      v.año AS year,
      v.base_imponible AS taxable_base,
      JSON_OBJECT(
        'id', u.id_usuario,
        'name', u.nombre,
        'lastname', u.apellido,
        'email', u.correo,
        'state', u.estado
      ) AS user
    FROM vehiculos v
    JOIN usuarios u
      ON v.id_usuario = u.id_usuario
    WHERE v.id_vehiculo = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getByPlate($plate)
  {
    $sql = "SELECT
      v.id_vehiculo AS id,
      v.placa AS plate,
      v.marca AS brand,
      v.modelo AS model,
      v.año AS year,
      v.base_imponible AS taxable_base,
      JSON_OBJECT(
        'id', u.id_usuario,
        'name', u.nombre,
        'lastname', u.apellido,
        'email', u.correo,
        'state', u.estado
      ) AS user
    FROM vehiculos v
    JOIN usuarios u
      ON v.id_usuario = u.id_usuario
    WHERE v.placa = :plate
  ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['plate' => $plate]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // Actualizar un vehículo
  public function update($id, $data)
  {
    $sql = "UPDATE vehiculos
    SET
      placa = :plate,
      marca = :brand,
      modelo = :model,
      año = :year,
      base_imponible = :taxable_base,
      id_usuario = :id_user
    WHERE id_vehiculo = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $data['id'] = $id;
    return $stmt->execute($data);
  }

  // Eliminar un vehículo
  public function delete($id)
  {
    $sql = "DELETE FROM vehiculos WHERE id_vehiculo = :id";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id]);
  }
}
