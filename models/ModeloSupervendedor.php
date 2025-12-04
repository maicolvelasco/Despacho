<?php
// models/ModeloSupervendedor.php

class ModeloSupervendedor {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todos los envíos filtrados por departamento
     *
     * @param int $department_id
     * @return array
     */
    public function obtenerEnviosPorDepartamento($department_id) {
        $sql = "
            SELECT 
                DISTINCT e.id AS envio_id,
                e.conductor,
                e.placa,
                e.fecha_inicio,
                e.fecha_fin,
                r.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                su.nombre AS supervendedor_nombre,
                e.estado,
                SUM(rp.cantidad) AS total_pallets,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre,
                COALESCE(d.nombre, 'N/A') AS departamento_origen,
                p.departamento_id AS pallet_department_id
            FROM 
                envios e
            JOIN 
                remision r ON e.remision_id = r.id
            LEFT JOIN 
                clientes c ON e.cliente_id = c.id
            JOIN 
                transporte t ON e.transporte_id = t.id
            LEFT JOIN 
                usuarios u ON e.usuario_id = u.id
            LEFT JOIN 
                remision_pallets rp ON rp.envio_id = e.id AND rp.tipo = 'envio'
            LEFT JOIN 
                titular tit ON e.titular_id = tit.id
            LEFT JOIN 
                usuarios su ON tit.usuario_id = su.id
            JOIN 
                remision_pallets rp_filter ON rp_filter.envio_id = e.id
            JOIN 
                pallets p ON rp_filter.pallet_id = p.id
            LEFT JOIN 
                departamentos d ON p.departamento_id = d.id
            WHERE 
                u.departamento_id = :department_id_u
                AND su.departamento_id = :department_id_su
            GROUP BY 
                e.id, p.departamento_id
            ORDER BY 
                e.fecha_inicio DESC
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'department_id_u' => $department_id,
                'department_id_su' => $department_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEnviosPorDepartamento: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerRecibirEnTransitoPorDepartamento($department_id) {
        $sql = "
            SELECT 
                DISTINCT rcv.id AS recibir_id,
                rcv.conductor,
                rcv.placa,
                rcv.fecha,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                su.nombre AS supervendedor_nombre,
                rcv.estado,
                SUM(rp.cantidad) AS total_pallets,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre,
                COALESCE(d.nombre, 'N/A') AS departamento_origen,
                p.departamento_id AS pallet_department_id
            FROM 
                recibir rcv
            JOIN 
                remision rm ON rcv.remision_id = rm.id
            LEFT JOIN 
                clientes c ON rcv.cliente_id = c.id
            JOIN 
                transporte t ON rcv.transporte_id = t.id
            LEFT JOIN 
                usuarios u ON rcv.usuario_id = u.id
            LEFT JOIN 
                remision_pallets rp ON rp.recibir_id = rcv.id AND rp.tipo = 'recibir'
            LEFT JOIN 
                titular tit ON rcv.titular_id = tit.id
            LEFT JOIN 
                usuarios su ON tit.usuario_id = su.id
            JOIN 
                remision_pallets rp_filter ON rp_filter.recibir_id = rcv.id
            JOIN 
                pallets p ON rp_filter.pallet_id = p.id
            LEFT JOIN 
                departamentos d ON p.departamento_id = d.id
            WHERE 
                rcv.estado = 'en_transito'
                AND u.departamento_id = :department_id_u
                AND su.departamento_id = :department_id_su
            GROUP BY 
                rcv.id, p.departamento_id
            ORDER BY 
                rcv.fecha DESC
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'department_id_u' => $department_id,
                'department_id_su' => $department_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerRecibirEnTransitoPorDepartamento: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerRecibirRecibidosPorDepartamento($department_id) {
        $sql = "
            SELECT 
                DISTINCT rcv.id AS recibir_id,
                rcv.conductor,
                rcv.placa,
                rcv.fecha,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                su.nombre AS supervendedor_nombre,
                rcv.estado,
                SUM(rp.cantidad) AS total_pallets,
                (
                    SELECT SUM(rp_recibido.cantidad)
                    FROM remision_pallets rp_recibido
                    JOIN recibir rcv_recibido ON rp_recibido.recibir_id = rcv_recibido.id
                    WHERE rp_recibido.remision_id = rm.id
                        AND rp_recibido.tipo = 'recibir'
                ) AS total_pallets_recibidos_total,
                (
                    SELECT SUM(rp_envio.cantidad)
                    FROM remision_pallets rp_envio
                    WHERE rp_envio.remision_id = rm.id AND rp_envio.tipo = 'envio'
                ) AS total_pallets_enviados,
                CASE 
                    WHEN (
                        SELECT SUM(rp_recibido.cantidad)
                        FROM remision_pallets rp_recibido
                        JOIN recibir rcv_recibido ON rp_recibido.recibir_id = rcv_recibido.id
                        WHERE rp_recibido.remision_id = rm.id
                            AND rp_recibido.tipo = 'recibir'
                    ) >= (
                        SELECT SUM(rp_envio.cantidad)
                        FROM remision_pallets rp_envio
                        WHERE rp_envio.remision_id = rm.id AND rp_envio.tipo = 'envio'
                    ) THEN 1 
                    ELSE 0 
                END AS remision_completada,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre,
                COALESCE(d.nombre, 'N/A') AS departamento_origen,
                p.departamento_id AS pallet_department_id
            FROM 
                recibir rcv
            JOIN 
                remision rm ON rcv.remision_id = rm.id
            LEFT JOIN 
                clientes c ON rcv.cliente_id = c.id
            JOIN 
                transporte t ON rcv.transporte_id = t.id
            LEFT JOIN 
                usuarios u ON rcv.usuario_id = u.id
            LEFT JOIN 
                remision_pallets
                rp ON rp.recibir_id = rcv.id AND rp.tipo = 'recibir'
            LEFT JOIN 
                titular tit ON rcv.titular_id = tit.id
            LEFT JOIN 
                usuarios su ON tit.usuario_id = su.id
            JOIN 
                remision_pallets rp_filter ON rp_filter.recibir_id = rcv.id
            JOIN 
                pallets p ON rp_filter.pallet_id = p.id
            LEFT JOIN 
                departamentos d ON p.departamento_id = d.id
            WHERE 
                rcv.estado = 'recibido'
                AND u.departamento_id = :department_id_u
                AND su.departamento_id = :department_id_su
            GROUP BY 
                rcv.id, p.departamento_id
            ORDER BY 
                rcv.fecha DESC
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'department_id_u' => $department_id,
                'department_id_su' => $department_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerRecibirRecibidosPorDepartamento: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerEnviosEnTransitoPorDepartamento($department_id) {
        $sql = "
            SELECT 
                DISTINCT e.id AS envio_id,
                e.conductor,
                e.placa,
                e.fecha_inicio,
                e.fecha_fin,
                r.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                su.nombre AS supervendedor_nombre,
                e.estado,
                SUM(rp.cantidad) AS total_pallets,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre,
                COALESCE(d.nombre, 'N/A') AS departamento_origen
            FROM 
                envios e
            JOIN 
                remision r ON e.remision_id = r.id
            LEFT JOIN 
                clientes c ON e.cliente_id = c.id
            JOIN 
                transporte t ON e.transporte_id = t.id
            LEFT JOIN 
                usuarios u ON e.usuario_id = u.id
            LEFT JOIN 
                remision_pallets rp ON rp.envio_id = e.id AND rp.tipo = 'envio'
            LEFT JOIN 
                titular tit ON e.titular_id = tit.id
            LEFT JOIN 
                usuarios su ON tit.usuario_id = su.id
            JOIN 
                remision_pallets rp_filter ON rp_filter.envio_id = e.id
            JOIN 
                pallets p ON rp_filter.pallet_id = p.id
            LEFT JOIN 
                departamentos d ON p.departamento_id = d.id
            WHERE 
                e.estado = 'en_transito'
                AND u.departamento_id = :department_id_u
                AND su.departamento_id = :department_id_su
            GROUP BY 
                e.id, d.nombre
            ORDER BY 
                e.fecha_inicio DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'department_id_u' => $department_id,
                'department_id_su' => $department_id
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEnviosEnTransitoPorDepartamento: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Aceptar una recepción (actualizar su estado a 'recibido')
     *
     * @param int $recibir_id
     * @return int Número de filas afectadas
     */
// En ModeloSupervendedor.php
    public function aceptarRecepcion($id) {
        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();
    
            // Actualizar el estado de la recepción
            $sql = "UPDATE recibir SET estado = 'recibido' WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
    
            // Obtener información del recibo
            $sql = "SELECT * FROM recibir WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $recepcion = $stmt->fetch();
    
            if ($recepcion) {
                // Obtener el usuario que realizó la recepción
                $sqlUsuario = "SELECT usuario_id FROM recibir WHERE id = :recibir_id";
                $stmtUsuario = $this->pdo->prepare($sqlUsuario);
                $stmtUsuario->execute(['recibir_id' => $id]);
                $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
    
                // Obtener los pallets asociados a esta recepción
                $sqlPallets = "
                    SELECT rp.pallet_id, rp.cantidad, p.tamano
                    FROM remision_pallets rp
                    JOIN pallets p ON rp.pallet_id = p.id
                    WHERE rp.recibir_id = :recibir_id AND rp.tipo = 'recibir'
                ";
                $stmtPallets = $this->pdo->prepare($sqlPallets);
                $stmtPallets->execute(['recibir_id' => $id]);
                $pallets = $stmtPallets->fetchAll(PDO::FETCH_ASSOC);
    
                // Sumar el stock de los pallets
                foreach ($pallets as $pallet) {
                    // Actualizar el stock del pallet
                    $stmtUpdateStock = $this->pdo->prepare("UPDATE pallets SET stock = stock + :cantidad WHERE id = :pallet_id");
                    $stmtUpdateStock->execute([
                        'cantidad' => $pallet['cantidad'],
                        'pallet_id' => $pallet['pallet_id']
                    ]);
    
                    // Verificar el rol del usuario
                    if ($usuario) {
                        $sqlRol = "SELECT rol_id FROM usuarios WHERE id = :usuario_id";
                        $stmtRol = $this->pdo->prepare($sqlRol);
                        $stmtRol->execute(['usuario_id' => $usuario['usuario_id']]);
                        $rol = $stmtRol->fetchColumn();
    
                        // Si el rol es 4, sumar pallets al stock del departamento del usuario
                        if ($rol == 4) {
                            // Obtener el departamento del usuario
                            $sqlDepartamento = "SELECT departamento_id FROM usuarios WHERE id = :usuario_id";
                            $stmtDepartamento = $this->pdo->prepare($sqlDepartamento);
                            $stmtDepartamento->execute(['usuario_id' => $usuario['usuario_id']]);
                            $departamento = $stmtDepartamento->fetchColumn();
    
                            // Sumar los pallets al stock del departamento
                            $stmtUpdateDepartamentoStock = $this->pdo->prepare("
                                UPDATE pallets 
                                SET stock = stock - :cantidad 
                                WHERE departamento_id = :departamento_id 
                                AND tamano = :tamano
                                LIMIT 1
                            ");
                            $stmtUpdateDepartamentoStock->execute([
                                'cantidad' => $pallet['cantidad'],
                                'departamento_id' => $departamento,
                                'tamano' => $pallet['tamano']
                            ]);
                        }
                    }
                }
            }
    
            // Commit de la transacción
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Rollback si algo sale mal
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
        /**
     * Aceptar un envío (actualizar su estado a 'entregado')
     *
     * @param int $envio_id
     * @return int Número de filas afectadas
     */
    public function aceptarEnvio($envio_id) {
        // Iniciar una transacción para asegurarnos de que el stock y el estado del envío se actualicen de manera atómica
        $this->pdo->beginTransaction();
    
        try {
            // Cambiar el estado del envío a "completado"
            $sqlUpdateEnvio = "UPDATE envios SET estado = 'completado' WHERE id = :id";
            $stmtEnvio = $this->pdo->prepare($sqlUpdateEnvio);
            $stmtEnvio->execute(['id' => $envio_id]);
    
            // Obtener los pallets asociados a este envío
            $sqlPallets = "
                SELECT rp.pallet_id, rp.cantidad, p.tamano
                FROM remision_pallets rp
                JOIN pallets p ON rp.pallet_id = p.id
                WHERE rp.envio_id = :envio_id AND rp.tipo = 'envio'
            ";
            $stmtPallets = $this->pdo->prepare($sqlPallets);
            $stmtPallets->execute(['envio_id' => $envio_id]);
            $pallets = $stmtPallets->fetchAll(PDO::FETCH_ASSOC);
    
            // Restar el stock de los pallets
            foreach ($pallets as $pallet) {
                $stmtCheckStock = $this->pdo->prepare("SELECT stock FROM pallets WHERE id = :pallet_id");
                $stmtCheckStock->execute(['pallet_id' => $pallet['pallet_id']]);
                $currentStock = $stmtCheckStock->fetchColumn();
    
                if ($currentStock < $pallet['cantidad']) {
                    throw new Exception("Stock insuficiente para el pallet ID {$pallet['pallet_id']}. Stock disponible: {$currentStock}");
                }
    
                // Actualizar el stock del pallet
                $stmtUpdateStock = $this->pdo->prepare("UPDATE pallets SET stock = stock - :cantidad WHERE id = :pallet_id");
                $stmtUpdateStock->execute([
                    'cantidad' => $pallet['cantidad'],
                    'pallet_id' => $pallet['pallet_id']
                ]);
            }
    
            // Obtener el usuario que realizó el envío
            $sqlUsuario = "SELECT usuario_id FROM envios WHERE id = :envio_id";
            $stmtUsuario = $this->pdo->prepare($sqlUsuario);
            $stmtUsuario->execute(['envio_id' => $envio_id]);
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
    
            if ($usuario) {
                // Verificar el rol del usuario
                $sqlRol = "SELECT rol_id FROM usuarios WHERE id = :usuario_id";
                $stmtRol = $this->pdo->prepare($sqlRol);
                $stmtRol->execute(['usuario_id' => $usuario['usuario_id']]);
                $rol = $stmtRol->fetchColumn();
    
                // Si el rol es 4, sumar pallets al stock del departamento del usuario
                if ($rol == 4) {
                    // Obtener el departamento del usuario
                    $sqlDepartamento = "SELECT departamento_id FROM usuarios WHERE id = :usuario_id";
                    $stmtDepartamento = $this->pdo->prepare($sqlDepartamento);
                    $stmtDepartamento->execute(['usuario_id' => $usuario['usuario_id']]);
                    $departamento = $stmtDepartamento->fetchColumn();
    
                    // Sumar los pallets al stock del departamento
                    foreach ($pallets as $pallet) {
                        $stmtUpdateDepartamentoStock = $this->pdo->prepare("
                            UPDATE pallets 
                            SET stock = stock + :cantidad 
                            WHERE departamento_id = :departamento_id 
                            AND tamano = :tamano
                            LIMIT 1
                        ");
                        $stmtUpdateDepartamentoStock->execute([
                            'cantidad' => $pallet['cantidad'],
                            'departamento_id' => $departamento,
                            'tamano' => $pallet['tamano']
                        ]);
                    }
                }
            }
    
            // Confirmar la transacción
            $this->pdo->commit();
            return true;
    
        } catch (Exception $e) {
            // Si ocurre algún error, deshacer la transacción
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Eliminar una recepción
     *
     * @param int $recibir_id
     * @return int Número de filas afectadas
     */
    public function eliminarRecepcion($recibir_id) {
        $sql = "DELETE FROM recibir WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $recibir_id]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error en eliminarRecepcion: " . $e->getMessage());
            return 0;
        }
    }

    /* === Funcionalidades de ModeloSupervendedor Integradas === */

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

    /**
     * Obtener el ID del departamento basado en el ID del usuario.
     *
     * @param int $user_id
     * @return int|null
     */
    public function getDepartmentIdByUserId($user_id) {
        $sql = "SELECT departamento_id FROM usuarios WHERE id = :user_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['departamento_id'] ?? null;
        } catch (PDOException $e) {
            error_log("Error en getDepartmentIdByUserId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener el total de pallets para un departamento específico.
     *
     * @param int $department_id
     * @return int
     */
    public function getTotalPalletsByDepartment($department_id) {
        $sql = "SELECT SUM(stock) AS total FROM pallets WHERE departamento_id = :department_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['department_id' => $department_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en getTotalPalletsByDepartment: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener el contador de caducados para un departamento específico.
     *
     * @param int $department_id
     * @return int
     */
    public function getCaducadosContadorByDepartment($department_id) {
        $today = date('Y-m-d');
        $sql = "
            SELECT COUNT(DISTINCT r.id) AS contador
            FROM remision r
            JOIN remision_pallets rp ON r.id = rp.remision_id
            JOIN pallets p ON rp.pallet_id = p.id
            JOIN envios e ON rp.envio_id = e.id
            WHERE p.departamento_id = :department_id
              AND e.fecha_fin <= :today
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
                  JOIN pallets p_rec ON rp_rec.pallet_id = p_rec.id
                  WHERE rp_rec.remision_id = r.id
                    AND rp_rec.tipo = 'recibir'
                    AND p_rec.departamento_id = :department_id
              )
        ";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['today' => $today, 'department_id' => $department_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['contador'] ?? 0);
        } catch (PDOException $e) {
            // Registrar el error en el log del servidor
            error_log("Error en getCaducadosContadorByDepartment: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener pallets del departamento específico.
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
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['department_id' => $department_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getPalletsByDepartment: " . $e->getMessage());
            return [];
        }
    }
}
?>