<?php

namespace App\Models;

use PDO;
use App\Config\Database;

class TicketModel
{
  private PDO $conn;

  public function __construct()
  {
    $this->conn = Database::getConnection();
  }

  public function create($data)
  {
    $this->conn->beginTransaction();

    try {
      $sql = "INSERT INTO tickets
        (id_vehiculo, id_espacio, fecha_entrada, estado, id_empleado)
      VALUES
        (:id_vehicle, :id_space, :entry_date, :state, :id_employ)
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

  public function allByPlate(string $plate)
  {
    $sql = "SELECT
      t.id_ticket AS id,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS state,
      DATE_ADD(t.fecha_entrada, INTERVAL z.tiempo_maximo MINUTE) AS max_date,
      JSON_OBJECT(
        'id', e.id_espacio,
        'state', e.estado,
        'type', e.tipo
      ) AS space,
      JSON_OBJECT(
        'id', z.id_zona,
        'name', z.nombre,
        'fee', z.tarifa,
        'max_time', z.tiempo_maximo,
        'address', z.address,
        'description', z.description
      ) AS zone,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'plate', v.placa,
        'brand', v.marca,
        'year', v.año,
        'year', v.año,
        'taxable_base', v.base_imponible
      ) AS vehicle,
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
    FROM tickets t
    JOIN vehiculos v
      ON t.id_vehiculo = v.id_vehiculo
    JOIN espacios e
      ON t.id_espacio = e.id_espacio
    JOIN zonas z
      ON e.id_zona = z.id_zona
    LEFT JOIN usuarios u
      ON u.id_usuario = v.id_usuario
    WHERE LOWER(v.placa) LIKE LOWER(:plate)
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute(["plate" => '%' . $plate . '%']);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function all($limit = 10, $offset = 0)
  {
    $sql = "SELECT
      t.id_ticket AS id,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS state,
      JSON_OBJECT(
        'id', e.id_espacio,
        'state', e.estado,
        'type', e.tipo
      ) AS space,
      JSON_OBJECT(
        'id', z.id_zona,
        'name', z.nombre,
        'fee', z.tarifa,
        'max_time', z.tiempo_maximo,
        'address', z.address,
        'description', z.description
      ) AS zone,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'plate', v.placa,
        'brand', v.marca,
        'year', v.año,
        'year', v.año,
        'taxable_base', v.base_imponible
      ) AS vehicle,
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
    FROM tickets t
    JOIN vehiculos v
      ON t.id_vehiculo = v.id_vehiculo
    JOIN espacios e
      ON t.id_espacio = e.id_espacio
    JOIN zonas z
      ON e.id_zona = z.id_zona
    LEFT JOIN usuarios u
      ON u.id_usuario = v.id_usuario
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
      t.id_ticket AS id,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS state,
      JSON_OBJECT(
        'id', e.id_espacio,
        'state', e.estado,
        'type', e.tipo
      ) AS space,
      JSON_OBJECT(
        'id', z.id_zona,
        'name', z.nombre,
        'fee', z.tarifa,
        'max_time', z.tiempo_maximo,
        'address', z.address,
        'description', z.description
      ) AS zone,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'plate', v.placa,
        'brand', v.marca,
        'year', v.año,
        'taxable_base', v.base_imponible
      ) AS vehicle,
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
    FROM tickets t
    JOIN vehiculos v ON t.id_vehiculo = v.id_vehiculo
    JOIN espacios e ON t.id_espacio = e.id_espacio
    JOIN zonas z ON e.id_zona = z.id_zona
    LEFT JOIN usuarios u ON u.id_usuario = v.id_usuario
    WHERE t.id_ticket = :id";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function complete($id, $data)
  {
    $sql = "UPDATE tickets
    SET
      fecha_salida = :exit_date,
      monto = :amount,
      estado = :state
    WHERE id_ticket = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $data['id'] = $id;
    return $stmt->execute($data);
  }

  public function cancel($id)
  {
    $sql = "UPDATE tickets
    SET
      estado = 'cancelado'
    WHERE id_ticket = :id
    ";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id]);
  }

  public function update($id, $data)
  {
    $sql = "UPDATE tickets
    SET
      placa = :plate,
      fecha_entrada = :entry_date,
      fecha_salida = :exit_date,
      monto = :amount,
      estado = :state,
      id_usuario = :id_user,
      id_espacio = :id_space,
    WHERE id_ticket = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $data['id'] = $id;
    return $stmt->execute($data);
  }

  public function delete($id)
  {
    $sql = "DELETE FROM tickets WHERE id_ticket = :id";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id]);
  }

  public function getTicketsFromUser(int $userId)
  {
    $sql = "SELECT
      t.id_ticket AS id,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS state,
      DATE_ADD(t.fecha_entrada, INTERVAL z.tiempo_maximo MINUTE) AS max_date,
      v.placa AS plate,
      z.nombre AS zone_name,
      JSON_OBJECT(
        'id', e.id_espacio,
        'state', e.estado,
        'type', e.tipo
      ) AS space,
      JSON_OBJECT(
        'id', z.id_zona,
        'name', z.nombre,
        'fee', z.tarifa,
        'max_time', z.tiempo_maximo,
        'address', z.address,
        'description', z.description
      ) AS zone,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'plate', v.placa,
        'brand', v.marca,
        'year', v.año,
        'taxable_base', v.base_imponible
      ) AS vehicle
    FROM tickets t
    JOIN vehiculos v ON t.id_vehiculo = v.id_vehiculo
    JOIN espacios e ON t.id_espacio = e.id_espacio
    JOIN zonas z ON e.id_zona = z.id_zona
    WHERE v.id_usuario = :userId";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getByEmployee(int $employeeId)
  {
    $sql = "SELECT
      t.id_ticket AS id,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS state,
      JSON_OBJECT(
        'id', e.id_espacio,
        'state', e.estado,
        'type', e.tipo
      ) AS space,
      JSON_OBJECT(
        'id', z.id_zona,
        'name', z.nombre,
        'fee', z.tarifa,
        'max_time', z.tiempo_maximo,
        'address', z.address,
        'description', z.description
      ) AS zone,
      JSON_OBJECT(
        'id', v.id_vehiculo,
        'plate', v.placa,
        'brand', v.marca,
        'year', v.año,
        'taxable_base', v.base_imponible
      ) AS vehicle,
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
    FROM tickets t
    JOIN vehiculos v ON t.id_vehiculo = v.id_vehiculo
    JOIN espacios e ON t.id_espacio = e.id_espacio
    JOIN zonas z ON e.id_zona = z.id_zona
    LEFT JOIN usuarios u ON u.id_usuario = v.id_usuario
    JOIN empleados emp ON t.id_empleado = emp.id_empleado
    WHERE emp.id_empleado = :employeeId";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
