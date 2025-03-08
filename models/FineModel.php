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
      m.evidencia AS mime,
      m.estado AS state,
      m.fecha_pago AS pay_date,
      JSON_OBJECT(
        'id', t.id_ticket,
        'id_user', t.id_usuario,
        'plate', t.placa,
        'entry_date', t.fecha_entrada,
        'exit_date', t.fecha_salida,
        'amount', t.monto,
        'state', t.estado
      ) AS ticket
    FROM multas as m
    JOIN tickets t
      ON t.id_ticket = m.id_ticket
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
      m.id_multa AS id,
      m.monto AS amount,
      m.descripcion AS description,
      m.evidencia AS mime,
      m.estado AS state,
      m.fecha_pago AS pay_date,
      JSON_OBJECT(
        'id', t.id_ticket,
        'id_user', t.id_usuario,
        'plate', t.placa,
        'entry_date', t.fecha_entrada,
        'exit_date', t.fecha_salida,
        'amount', t.monto,
        'state', t.estado
      ) AS ticket
    FROM multas as m
    JOIN tickets t
      ON t.id_ticket = m.id_ticket
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
      (id_ticket, monto, descripcion, evidencia, estado)
    VALUES
      (:id_ticket, :amount, :description, :mime, 'pendiente')
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

  public function getFinesFromUser(int $id)
  {
    $sql = "SELECT
      m.id_multa as id,
      m.monto as amount,
      m.evidencia as mime,
      m.estado as state,
      t.placa as plate
    FROM multas m
    JOIN tickets t
      ON t.id_ticket = m.id_ticket
    WHERE t.id_usuario = :id AND m.estado = 'pendiente'
    ORDER BY m.monto DESC
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
