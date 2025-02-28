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

  public function create($data)
  {
    $sql = "INSERT INTO multas
      (id_ticket, monto, descripcion, evidencia, estado)
    VALUES
      (:id_ticket, :amount, :description, :mime, :state)
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
