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
    $sql = "INSERT INTO tickets
      (id_usuario, id_espacio, placa, fecha_entrada, fecha_salida, monto, estado)
    VALUES
      (:id_user, :id_space, :plate, :entry_date, :exit_date, :amount, :status)
    ";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute($data);
  }

  public function all($limit = 10, $offset = 0)
  {
    $sql = "SELECT
      t.id_ticket AS id,
      t.placa AS plate,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS status,
      JSON_OBJECT(
        'id', u.id_usuario,
        'name', u.nombre,
        'lastname', u.apellido,
        'email', u.correo,
        'state', u.estado
      ) AS user,
      JSON_OBJECT(
        'id', e.id_espacio,
        'state', e.estado,
        'type', e.tipo
      ) AS space
    FROM tickets t
    JOIN usuarios u
      ON t.id_usuario = u.id_usuario
    JOIN espacios e
      ON t.id_espacio = e.id_espacio
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
      t.placa AS plate,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS status,
      JSON_OBJECT(
        'id', u.id_usuario,
        'name', u.nombre,
        'lastname', u.apellido,
        'email', u.correo,
        'state', u.estado
      ) AS user,
      JSON_OBJECT(
        'id', e.id_espacio,
        'state', e.estado,
        'type', e.tipo
      ) AS space
    FROM tickets t
    JOIN usuarios u
      ON t.id_usuario = u.id_usuario
    JOIN espacios e
      ON t.id_espacio = e.id_espacio
    WHERE t.id_ticket = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
}
