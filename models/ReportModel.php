<?php

namespace App\Models;

use PDO;
use App\Config\Database;
use App\Utils\HttpError;
use PDOException;

class ReportModel
{
  private PDO $conn;

  public function __construct()
  {
    $this->conn = Database::getConnection();
  }

  public function activeUsers()
  {
    // Usuarios activos mes actual - anterior mes
    $sql = "SELECT
      COUNT(
        DISTINCT  CASE
          WHEN (YEAR(fecha_entrada) = YEAR(CURDATE()) AND MONTH(fecha_entrada) = MONTH(CURDATE()))
            OR (fecha_salida IS NOT NULL AND YEAR(fecha_salida) = YEAR(CURDATE()) AND MONTH(fecha_salida) = MONTH(CURDATE()))
          THEN
            id_usuario
          ELSE NULL
        END
      ) AS current_month,
      COUNT(
        DISTINCT  CASE
          WHEN (YEAR(fecha_entrada) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(fecha_entrada) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)))
            OR (fecha_salida IS NOT NULL AND YEAR(fecha_salida) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(fecha_salida) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)))
          THEN
            id_usuario
          ELSE NULL
        END
      ) AS last_month
    FROM tickets;
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function earnToday()
  {
    // Ganancias de hoy
    $sql = "SELECT
    CAST((
      SELECT IFNULL(SUM(monto), 0)
      FROM tickets
      WHERE estado = 'finalizado'
      AND fecha_salida IS NOT NULL
      AND DATE(fecha_salida) = CURDATE()
    )
      +
    (
      SELECT IFNULL(SUM(monto), 0)
      FROM multas
      WHERE fecha_pago IS NOT NULL
      AND DATE(fecha_pago) = CURDATE()
    ) AS FLOAT) AS total;
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function spacesTaken()
  {
    //-- Ocupados
    $sql = "SELECT
      tt.total,
      tt.taken,
      (tt.taken / tt.total) * 100 AS percent
    FROM (
      SELECT
        COUNT(e.id_espacio) total,
        CAST(SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS FLOAT) taken
      FROM espacios e
    ) as tt;
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function fees()
  {
    // Multas
    $sql = "SELECT
      COUNT(*) AS total,
      CAST(SUM(m.monto) AS FLOAT) AS amount
    FROM multas m
    WHERE m.estado = 'pendiente';
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function earnByMonth()
  {
    // -- ESTADISTICAS POR MES
    $sql = "SELECT
    tt.year,
      tt.month,
      SUM(tt.total) AS total
    FROM (
    SELECT
      YEAR(t.fecha_salida) as year,
      MONTH(t.fecha_salida) as month,
      SUM(t.monto) as total
    FROM  tickets t
    WHERE t.estado = 'finalizado' AND t.fecha_salida  IS NOT NULL
    GROUP BY year, month

    UNION ALL

    SELECT
        YEAR(m.fecha_pago) AS year,
        MONTH(m.fecha_pago) AS month,
        CAST(SUM(m.monto) AS FLOAT) AS total
    FROM multas m
    WHERE m.fecha_pago IS NOT NULL
    GROUP BY year, month
    ) as tt
    GROUP BY tt.year, tt.month
    ORDER BY tt.year DESC, tt.month DESC;
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function eachSpaceTaken()
  {
    // -- Ocpados por Zonas
    $sql = "SELECT
      COALESCE(z.id_zona, 0) AS id,
      COUNT(*) AS total,
      CAST(SUM(CASE WHEN e.estado = 'ocupado' THEN 1 ELSE 0 END) AS SIGNED) AS taken
    FROM zonas z
    JOIN espacios e ON z.id_zona = e.id_zona
    GROUP BY z.id_zona;
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function usersByRol()
  {
    // -- Cantidad de usuarios por ROL [CEHECKS]
    $sql = "SELECT
      CAST(SUM(CASE WHEN r.nombre_rol = 'admin' THEN 1 ELSE 0 END) AS SIGNED) as admin,
      CAST(SUM(CASE WHEN r.nombre_rol = 'empleado' THEN 1 ELSE 0 END) AS SIGNED) as employ,
      CAST(SUM(CASE WHEN r.nombre_rol = 'cliente' THEN 1 ELSE 0 END) AS SIGNED) as client
    FROM usuarios u
    JOIN roles r
    ON u.id_rol  = r.id_rol;";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
