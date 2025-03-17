<?php

namespace App\Models;

use PDO;
use App\Config\Database;
use App\Utils\HttpError;
use PDOException;

class FineModel
{
  private PDO $conn;

  public function __construct()
  {
    $this->conn = Database::getConnection();
  }

  public function all($limit = 10, $offset = 0)
  {
    $sql = "SELECT
      m.id_multa AS id,
      m.monto AS amount,
      m.descripcion AS description,
      m.evidencia AS filename,
      m.estado AS state,
      m.fecha_creacion AS created_date,
      m.fecha_pago AS pay_date,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'id_user', v.id_usuario,
        'plate', v.placa,
        'model', v.modelo,
        'year', v.año
      ) AS vehicle,
      JSON_OBJECT(
        'id', e.id_usuario,
        'name', e.nombre,
        'lastname', e.apellido,
        'role', r.nombre_rol
      ) AS employ
    FROM multas m
    JOIN vehiculos v
      ON m.id_vehiculo = v.id_vehiculo
    JOIN usuarios e
      ON m.id_empleado = e.id_usuario
    JOIN roles r
      ON e.id_rol = r.id_rol
    LIMIT :limit
    OFFSET :offset
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function allByPlate(string $plate)
  {
    $sql = "SELECT
      m.id_multa AS id,
      m.monto AS amount,
      m.descripcion AS description,
      m.evidencia AS filename,
      m.estado AS state,
      m.fecha_pago AS pay_date,
      m.fecha_creacion AS created_date,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'id_user', v.id_usuario,
        'plate', v.placa,
        'model', v.modelo,
        'year', v.año
      ) AS vehicle,
      JSON_OBJECT(
        'id', e.id_usuario,
        'name', e.nombre,
        'lastname', e.apellido,
        'role', r.nombre_rol
      ) AS employ
    FROM multas m
    JOIN vehiculos v
      ON m.id_vehiculo = v.id_vehiculo
    JOIN usuarios e
      ON m.id_empleado = e.id_usuario
    JOIN roles r
      ON e.id_rol = r.id_rol
    WHERE LOWER(v.placa) LIKE LOWER(:plate)
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute(['plate' => '%' . $plate . '%']);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get($id)
  {
    $sql = "SELECT
      m.id_multa AS id,
      m.monto AS amount,
      m.descripcion AS description,
      m.evidencia AS filename,
      m.estado AS state,
      m.fecha_pago AS pay_date,
      m.fecha_creacion AS created_date,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'id_user', v.id_usuario,
        'plate', v.placa,
        'model', v.modelo,
        'year', v.año
      ) AS vehicle,
      JSON_OBJECT(
        'id', e.id_usuario,
        'name', e.nombre,
        'lastname', e.apellido,
        'role', r.nombre_rol
      ) AS employ
    FROM multas m
    JOIN vehiculos v
      ON m.id_vehiculo = v.id_vehiculo
    JOIN usuarios e
      ON m.id_empleado = e.id_usuario
    JOIN roles r
      ON e.id_rol = r.id_rol
    WHERE m.id_multa = :id
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function create($data)
  {
    $sql = "INSERT INTO multas
      (id_vehiculo, id_empleado, fecha_creacion, monto, descripcion, evidencia, estado)
    VALUES
      (:id_vehicle, :id_employ, :created_date, :amount, :description, :filename, 'pendiente')
    ";
    $this->conn->beginTransaction();
    $stmt = $this->conn->prepare($sql);

    $result = $stmt->execute($data);

    $lastId = $this->conn->lastInsertId();

    $this->conn->commit();

    return $result ? $lastId : -1;
    try {
    } catch (PDOException $e) {
      $this->conn->rollBack();

      throw HttpError::BadRequest($e->getMessage());
    }
  }

  public function pay($id, $data)
  {
    $sql = "UPDATE multas
    SET
      estado = 'pagada',
      fecha_pago = :pay_date
    WHERE id_multa = :id
    ";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id, 'pay_date' => $data]);
  }

  public function cancel($id)
  {
    $sql = "UPDATE multas
    SET
      estado = 'cancelado'
    WHERE id_multa = :id
    ";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id]);
  }

  public function getByUser(int $id)
  {
    $sql = "SELECT
      m.id_multa AS id,
      m.monto AS amount,
      m.descripcion AS description,
      m.evidencia AS filename,
      m.estado AS state,
      m.fecha_pago AS pay_date,
      m.fecha_creacion AS created_date,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'id_user', v.id_usuario,
        'plate', v.placa,
        'model', v.modelo,
        'year', v.año
      ) AS vehicle,
      JSON_OBJECT(
        'id', e.id_usuario,
        'name', e.nombre,
        'lastname', e.apellido,
        'role', r.nombre_rol
      ) AS employ
    FROM multas m
    JOIN vehiculos v
      ON m.id_vehiculo = v.id_vehiculo
    JOIN usuarios e
      ON m.id_empleado = e.id_usuario
    JOIN roles r
      ON e.id_rol = r.id_rol
    WHERE v.id_usuario = :id_usuario
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':id_usuario', $id, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
