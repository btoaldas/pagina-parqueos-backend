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

  public function getWithNoUser()
  {
    $sql = "SELECT
      v.id_vehiculo AS id,
      v.placa AS plate,
      v.marca AS brand,
      v.modelo AS model,
      v.año AS year,
      v.base_imponible AS taxable_base
    FROM vehiculos v
    WHERE v.id_usuario IS NULL
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    $value = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $value;
  }

  public function getByUser(int $id)
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

  public function updateUser($idVehicle, $idUser)
  {
    $sql = "UPDATE vehiculos
    SET id_usuario = :idUser
    WHERE id_vehiculo = :idVehicle
    ";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['idUser' => $idUser, 'idVehicle' => $idVehicle]);
  }

  // Crear un nuevo vehículo
  public function create($data)
  {
    $this->conn->beginTransaction();

    try {
      $sql = "INSERT INTO vehiculos
        (id_usuario, placa, marca, modelo, año, base_imponible)
      VALUES
        (:id_user, :plate, :brand, :model, :year, :taxable_base)
      ";

      $stmt = $this->conn->prepare($sql);
      $stmt->execute($data);

      $id = $this->conn->lastInsertId();
      $this->conn->commit();

      return (int)$id;
    } catch (\Exception $e) {
      $this->conn->rollBack();
      throw $e;
    }
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
  public function all()
  {
    $sql = "SELECT
      v.id_vehiculo AS id,
      v.placa AS plate,
      v.marca AS brand,
      v.modelo AS model,
      v.año AS year,
      v.base_imponible AS taxable_base,
      CASE
        WHEN v.id_usuario IS NOT NULL THEN
        JSON_OBJECT(
          'id', u.id_usuario,
          'name', u.nombre,
          'lastname', u.apellido,
          'email', u.correo,
          'state', u.estado
        )
        ELSE NULL
      END AS user
    FROM vehiculos v
    LEFT JOIN usuarios u
      ON v.id_usuario = u.id_usuario
    ";

    $stmt = $this->conn->prepare($sql);

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
      CASE
        WHEN v.id_usuario IS NOT NULL THEN
        JSON_OBJECT(
          'id', u.id_usuario,
          'name', u.nombre,
          'lastname', u.apellido,
          'email', u.correo,
          'state', u.estado
        )
        ELSE NULL
      END AS user
    FROM vehiculos v
    LEFT JOIN usuarios u
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
      CASE
        WHEN v.id_usuario IS NOT NULL THEN
        JSON_OBJECT(
          'id', u.id_usuario,
          'name', u.nombre,
          'lastname', u.apellido,
          'email', u.correo,
          'state', u.estado
        )
        ELSE NULL
      END AS user
    FROM vehiculos v
    LEFT JOIN usuarios u
      ON v.id_usuario = u.id_usuario
    WHERE LOWER(v.placa) LIKE LOWER(:plate)
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['plate' => '%' . $plate . '%']);
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
