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

  public function getReportTicktes()
  {
    $sql = "SELECT
      t.id_ticket AS id,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS state,
      u.nombre AS user_name,
      u.apellido AS user_lastname,
      u.correo AS user_email,
      e.id_usuario AS employee_id,
      e.nombre AS employee_name,
      e.apellido AS employee_lastname,
      s.id_espacio AS space_id,
      s.estado AS space_state,
      s.tipo AS space_type,
      z.id_zona AS zone_id,
      z.nombre AS zone_name,
      z.tarifa AS zone_fee,
      z.tiempo_maximo AS zone_max_time,
      z.address AS zone_address,
      z.description AS zone_description,
      v.id_vehiculo AS vehicle_id,
      v.placa AS vehicle_plate,
      v.marca AS vehicle_brand,
      v.año AS vehicle_year,
      v.base_imponible AS vehicle_taxable_base
    FROM tickets t
    LEFT JOIN vehiculos v
      ON t.id_vehiculo = v.id_vehiculo
    LEFT JOIN usuarios u
      ON v.id_usuario = u.id_usuario
    JOIN usuarios e
      ON t.id_empleado = e.id_usuario
    JOIN espacios s
      ON t.id_espacio = s.id_espacio
    JOIN zonas z
      ON s.id_zona = z.id_zona
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getReportTicktesByUser($id)
  {
    $sql = "SELECT
      t.id_ticket AS id,
      t.fecha_entrada AS entry_date,
      t.fecha_salida AS exit_date,
      t.monto AS amount,
      t.estado AS state,
      s.id_espacio AS space_id,
      s.estado AS space_state,
      s.tipo AS space_type,
      z.id_zona AS zone_id,
      z.nombre AS zone_name,
      z.tarifa AS zone_fee,
      z.tiempo_maximo AS zone_max_time,
      z.address AS zone_address,
      z.description AS zone_description,
      v.id_vehiculo AS vehicle_id,
      v.placa AS vehicle_plate,
      v.marca AS vehicle_brand,
      v.año AS vehicle_year,
      v.base_imponible AS vehicle_taxable_base,
      u.nombre AS user_name,
      u.apellido AS user_lastname,
      u.correo AS user_email
    FROM tickets t
    LEFT JOIN vehiculos v
      ON t.id_vehiculo = v.id_vehiculo
    LEFT JOIN usuarios u
      ON v.id_usuario = u.id_usuario
    JOIN usuarios e
      ON t.id_empleado = e.id_usuario
    JOIN espacios s
      ON t.id_espacio = s.id_espacio
    JOIN zonas z
      ON s.id_zona = z.id_zona
    WHERE t.id_empleado = :id
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function activeUsers()
  {
    // Usuarios activos mes actual - anterior mes
    $sql = "SELECT
      COUNT(
        DISTINCT  CASE
          WHEN (YEAR(t.fecha_entrada) = YEAR(CURDATE()) AND MONTH(t.fecha_entrada) = MONTH(CURDATE()))
            OR (t.fecha_salida IS NOT NULL AND YEAR(t.fecha_salida) = YEAR(CURDATE()) AND MONTH(t.fecha_salida) = MONTH(CURDATE()))
          THEN
            id_usuario
          ELSE NULL
        END
      ) AS current_month,
      COUNT(
        DISTINCT  CASE
          WHEN (YEAR(t.fecha_entrada) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(t.fecha_entrada) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)))
            OR (t.fecha_salida IS NOT NULL AND YEAR(t.fecha_salida) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(t.fecha_salida) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)))
          THEN
            v.id_usuario
          ELSE NULL
        END
      ) AS last_month
    FROM tickets t
    JOIN vehiculos v
      ON t.id_vehiculo = v.id_vehiculo
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
