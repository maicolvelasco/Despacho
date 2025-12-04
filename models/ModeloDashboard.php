<?php

class ModeloDashboard {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Obtener el primer día y el último día del mes actual
    private function getCurrentMonthDateRange() {
        $firstDay = date('Y-m-01');
        $lastDay = date('Y-m-t');
        return [$firstDay, $lastDay];
    }

    // Obtener el primer día y el último día del año actual
    private function getCurrentYearDateRange() {
        $firstDay = date('Y-01-01');
        $lastDay = date('Y-12-31');
        return [$firstDay, $lastDay];
    }

    // Obtener el total de pallets (suma de stock de todos los pallets)
    public function getTotalPallets() {
        // Suponiendo que la tabla 'pallets' tiene una columna 'stock'
        $stmt = $this->pdo->query("SELECT SUM(stock) AS total FROM pallets");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtener departamentos que tienen pallets.
     *
     * @return array
     */
    public function getDepartmentsWithPallets() {
        $sql = "
            SELECT DISTINCT d.id, d.nombre
            FROM departamentos d
            JOIN pallets p ON d.id = p.departamento_id
            WHERE p.stock > 0
            ORDER BY d.nombre ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener tamaños de pallets y su stock por departamento.
     *
     * @param int $department_id
     * @return array
     */
    public function getPalletsByDepartment($department_id) {
        $sql = "
            SELECT p.tamano, p.stock
            FROM pallets p
            WHERE p.departamento_id = :department_id
            ORDER BY p.tamano ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['department_id' => $department_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
        /**
     * Obtener la suma de pallets por tamaño en todos los departamentos.
     *
     * @return array
     */
    public function getPalletsAllDepartments() {
        $sql = "
            SELECT p.tamano, SUM(p.stock) AS stock_total
            FROM pallets p
            WHERE p.stock > 0
            GROUP BY p.tamano
            ORDER BY p.tamano ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener el total de pallets enviados en el mes actual
    public function getTotalEnviados() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT SUM(rp.cantidad) AS total_enviados
            FROM remision_pallets rp
            JOIN envios e ON rp.envio_id = e.id
            WHERE rp.envio_id IS NOT NULL
              AND e.fecha_inicio BETWEEN :firstDay AND :lastDay
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        $result = $stmt->fetch();
        return $result['total_enviados'] ?? 0;
    }

    // Obtener el total de pallets recibidos en el mes actual
    public function getTotalRecibidos() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT SUM(rp.cantidad) AS total_recibidos
            FROM remision_pallets rp
            JOIN recibir r ON rp.recibir_id = r.id
            WHERE rp.recibir_id IS NOT NULL
              AND r.fecha BETWEEN :firstDay AND :lastDay
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        $result = $stmt->fetch();
        return $result['total_recibidos'] ?? 0;
    }

    // Obtener cantidad de enviados por departamento para el mes actual
    public function getEnviadosPorDepartamento() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT d.nombre AS departamento, SUM(rp.cantidad) AS total_enviados
            FROM remision_pallets rp
            JOIN envios e ON rp.envio_id = e.id
            JOIN titular t ON e.titular_id = t.id
            JOIN usuarios u ON t.usuario_id = u.id
            JOIN departamentos d ON u.departamento_id = d.id
            WHERE rp.envio_id IS NOT NULL
              AND e.fecha_inicio BETWEEN :firstDay AND :lastDay
            GROUP BY d.nombre
            ORDER BY total_enviados DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        return $stmt->fetchAll();
    }

    // Obtener cantidad de recibidos por departamento para el mes actual
    public function getRecibidosPorDepartamento() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT d.nombre AS departamento, SUM(rp.cantidad) AS total_recibidos
            FROM remision_pallets rp
            JOIN recibir r ON rp.recibir_id = r.id
            JOIN titular t ON r.titular_id = t.id
            JOIN usuarios u ON t.usuario_id = u.id
            JOIN departamentos d ON u.departamento_id = d.id
            WHERE rp.recibir_id IS NOT NULL
              AND r.fecha BETWEEN :firstDay AND :lastDay
            GROUP BY d.nombre
            ORDER BY total_recibidos DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        return $stmt->fetchAll();
    }

    // Obtener cantidad de enviados por tamaño de pallet para el mes actual
    public function getEnviadosPorTamanoPallet() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT p.tamano AS tamano_pallet, SUM(rp.cantidad) AS total_enviados
            FROM remision_pallets rp
            JOIN pallets p ON rp.pallet_id = p.id
            JOIN envios e ON rp.envio_id = e.id
            WHERE rp.envio_id IS NOT NULL
              AND e.fecha_inicio BETWEEN :firstDay AND :lastDay
            GROUP BY p.tamano
            ORDER BY total_enviados DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        return $stmt->fetchAll();
    }

    // Obtener datos anuales de envíos y recibidos
    public function getDatosAnuales() {
        list($firstDay, $lastDay) = $this->getCurrentYearDateRange();
        $sql = "
            SELECT 
                MONTH(e.fecha_inicio) AS mes,
                SUM(rp.cantidad) AS total_enviados
            FROM remision_pallets rp
            JOIN envios e ON rp.envio_id = e.id
            WHERE rp.envio_id IS NOT NULL
              AND e.fecha_inicio BETWEEN :firstDay AND :lastDay
            GROUP BY mes
            ORDER BY mes ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        $enviados = $stmt->fetchAll();

        $sql = "
            SELECT 
                MONTH(r.fecha) AS mes,
                SUM(rp.cantidad) AS total_recibidos
            FROM remision_pallets rp
            JOIN recibir r ON rp.recibir_id = r.id
            WHERE rp.recibir_id IS NOT NULL
              AND r.fecha BETWEEN :firstDay AND :lastDay
            GROUP BY mes
            ORDER BY mes ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        $recibidos = $stmt->fetchAll();

        // Inicializar arrays para los 12 meses
        $datosAnuales = [
            'enviados' => array_fill(1, 12, 0),
            'recibidos' => array_fill(1, 12, 0)
        ];

        foreach ($enviados as $item) {
            $mes = (int)$item['mes'];
            $datosAnuales['enviados'][$mes] = (int)$item['total_enviados'];
        }

        foreach ($recibidos as $item) {
            $mes = (int)$item['mes'];
            $datosAnuales['recibidos'][$mes] = (int)$item['total_recibidos'];
        }

        return $datosAnuales;
    }

    // Obtener cantidad de pallets enviados por vendedor para el mes actual
    public function getPalletsEnviadosPorVendedor() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT u.nombre AS vendedor, SUM(rp.cantidad) AS total_enviados
            FROM remision_pallets rp
            JOIN envios e ON rp.envio_id = e.id
            JOIN titular t ON e.titular_id = t.id
            JOIN usuarios u ON t.usuario_id = u.id
            WHERE rp.envio_id IS NOT NULL
              AND e.fecha_inicio BETWEEN :firstDay AND :lastDay
            GROUP BY u.nombre
            ORDER BY total_enviados DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        return $stmt->fetchAll();
    }
    
    public function getEnviadosPorDepartamentoPallets() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT 
                d.id AS departamento_id,
                d.nombre AS departamento,
                p.tamano,
                SUM(rp.cantidad) AS total_enviados
            FROM remision_pallets rp
            JOIN envios e ON rp.envio_id = e.id
            JOIN pallets p ON rp.pallet_id = p.id
            JOIN departamentos d ON p.departamento_id = d.id
            WHERE rp.envio_id IS NOT NULL
              AND e.fecha_inicio BETWEEN :firstDay AND :lastDay
            GROUP BY d.id, d.nombre, p.tamano
            ORDER BY d.nombre, p.tamano
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRecibidosPorDepartamentoPallets() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT 
                d.id AS departamento_id,
                d.nombre AS departamento,
                p.tamano,
                SUM(rp.cantidad) AS total_recibidos
            FROM remision_pallets rp
            JOIN recibir r ON rp.recibir_id = r.id
            JOIN pallets p ON rp.pallet_id = p.id
            JOIN departamentos d ON p.departamento_id = d.id
            WHERE rp.recibir_id IS NOT NULL
              AND r.fecha BETWEEN :firstDay AND :lastDay
            GROUP BY d.id, d.nombre, p.tamano
            ORDER BY d.nombre, p.tamano
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener cantidad de pallets recibidos por vendedor para el mes actual
    public function getPalletsRecibidosPorVendedor() {
        list($firstDay, $lastDay) = $this->getCurrentMonthDateRange();
        $sql = "
            SELECT u.nombre AS vendedor, SUM(rp.cantidad) AS total_recibidos
            FROM remision_pallets rp
            JOIN recibir r ON rp.recibir_id = r.id
            JOIN titular t ON r.titular_id = t.id
            JOIN usuarios u ON t.usuario_id = u.id
            WHERE rp.recibir_id IS NOT NULL
              AND r.fecha BETWEEN :firstDay AND :lastDay
            GROUP BY u.nombre
            ORDER BY total_recibidos DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['firstDay' => $firstDay, 'lastDay' => $lastDay]);
        return $stmt->fetchAll();
    }
    
    public function getCaducadosContador() {
        $today = date('Y-m-d');
        $sql = "
            SELECT COUNT(*) AS contador
            FROM remision r
            WHERE EXISTS (
                SELECT 1
                FROM envios e
                WHERE e.remision_id = r.id
                  AND e.fecha_fin <= :today
            )
            AND (
                SELECT SUM(rp_envio.cantidad)
                FROM remision_pallets rp_envio
                JOIN envios e_envio ON rp_envio.envio_id = e_envio.id
                WHERE rp_envio.remision_id = r.id
                  AND rp_envio.tipo = 'envio'
                  AND e_envio.fecha_fin <= :today
            ) > (
                SELECT IFNULL(SUM(rp_rec.cantidad), 0)
                FROM remision_pallets rp_rec
                WHERE rp_rec.remision_id = r.id
                  AND rp_rec.tipo = 'recibir'
            )
        ";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['today' => $today]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['contador'] ?? 0);
        } catch (PDOException $e) {
            // Registrar el error en el log del servidor
            error_log("Error en getCaducadosContador: " . $e->getMessage());
            return 0;
        }
    }
}
?>