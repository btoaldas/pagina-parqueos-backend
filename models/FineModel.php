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
}
