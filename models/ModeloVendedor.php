<?php 
// /models/ModeloVendedor.php

class ModeloVendedor {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtener recepciones recibidas del usuario logueado
     *
     * @param int $usuario_id
     * @return array
     */
    public function obtenerRecibirRecibidos($usuario_id) {
        $sql = " 
            SELECT 
                rcv.id AS recibir_id,
                rcv.conductor,
                rcv.placa,
                rcv.fecha AS fecha,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                SUM(rp.cantidad) AS total_pallets_recibidos,
                e.id AS envio_id,
                (
                    SELECT SUM(rp_envio.cantidad) 
                    FROM remision_pallets rp_envio 
                    WHERE rp_envio.remision_id = rm.id AND rp_envio.tipo = 'envio'
                ) AS total_pallets_enviados,
                (
                    SELECT SUM(rp_recibido.cantidad) 
                    FROM remision_pallets rp_recibido 
                    JOIN recibir rcv_recibido ON rp_recibido.recibir_id = rcv_recibido.id 
                    WHERE rp_recibido.remision_id = rm.id AND rp_recibido.tipo = 'recibir'
                ) AS total_pallets_recibidos_total,
                CASE 
                    WHEN (
                        SELECT SUM(rp_recibido.cantidad) 
                        FROM remision_pallets rp_recibido 
                        JOIN recibir rcv_recibido ON rp_recibido.recibir_id = rcv_recibido.id 
                        WHERE rp_recibido.remision_id = rm.id AND rp_recibido.tipo = 'recibir'
                    ) >= (
                        SELECT SUM(rp_envio.cantidad) 
                        FROM remision_pallets rp_envio 
                        WHERE rp_envio.remision_id = rm.id AND rp_envio.tipo = 'envio'
                    ) 
                    THEN 1 ELSE 0 
                END AS remision_completada,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre,
                COALESCE(d.nombre, 'N/A') AS departamento_origen
            FROM recibir rcv
            JOIN remision rm ON rcv.remision_id = rm.id
            LEFT JOIN clientes c ON rcv.cliente_id = c.id
            JOIN transporte t ON rcv.transporte_id = t.id
            LEFT JOIN usuarios u ON rcv.usuario_id = u.id
            LEFT JOIN remision_pallets rp ON rp.recibir_id = rcv.id AND rp.tipo = 'recibir'
            JOIN envios e ON e.remision_id = rcv.remision_id
            LEFT JOIN titular tit ON rcv.titular_id = tit.id
            LEFT JOIN pallets p ON rp.pallet_id = p.id
            LEFT JOIN departamentos d ON p.departamento_id = d.id
            WHERE 
                rcv.estado IN ('recibido', 'en_transito') 
                AND rcv.usuario_id = :usuario_id
            GROUP BY rcv.id
            ORDER BY rcv.fecha DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerEnviosEnTransito($usuario_id) {
        $sql = " 
            SELECT 
                e.id AS envio_id,
                e.conductor,
                e.placa,
                e.fecha_inicio,
                e.fecha_fin,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                SUM(rp.cantidad) AS total_pallets,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre,
                COALESCE(d.nombre, 'N/A') AS departamento_origen
            FROM envios e
            JOIN remision rm ON e.remision_id = rm.id
            LEFT JOIN clientes c ON e.cliente_id = c.id
            JOIN transporte t ON e.transporte_id = t.id
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN remision_pallets rp ON rp.envio_id = e.id AND rp.tipo = 'envio'
            LEFT JOIN titular tit ON e.titular_id = tit.id
            LEFT JOIN pallets p ON rp.pallet_id = p.id
            LEFT JOIN departamentos d ON p.departamento_id = d.id
            WHERE 
                e.estado = 'en_transito' 
                AND e.usuario_id = :usuario_id
            GROUP BY e.id
            ORDER BY e.fecha_inicio DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerEnviosCompletados($usuario_id) {
        $sql = " 
            SELECT 
                e.id AS envio_id,
                e.conductor,
                e.placa,
                e.fecha_inicio,
                e.fecha_fin,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                SUM(rp.cantidad) AS total_pallets,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre,
                COALESCE(d.nombre, 'N/A') AS departamento_origen
            FROM envios e
            JOIN remision rm ON e.remision_id = rm.id
            LEFT JOIN clientes c ON e.cliente_id = c.id
            JOIN transporte t ON e.transporte_id = t.id
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN remision_pallets rp ON rp.envio_id = e.id AND rp.tipo = 'envio'
            LEFT JOIN titular tit ON e.titular_id = tit.id
            LEFT JOIN pallets p ON rp.pallet_id = p.id
            LEFT JOIN departamentos d ON p.departamento_id = d.id
            WHERE 
                e.estado = 'completado' 
                AND e.usuario_id = :usuario_id
            GROUP BY e.id
            ORDER BY e.fecha_inicio DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Aceptar un envío (actualizar su estado a 'completado' y reducir el stock)
     *
     * @param int $envio_id
     * @param int $usuario_id
     * @return int Número de filas afectadas
     */
    public function aceptarEnvio($envio_id, $usuario_id) {
        // Iniciar una transacción para asegurarnos de que el stock y el estado del envío se actualicen de manera atómica
        $this->pdo->beginTransaction();
    
        try {
            // Cambiar el estado del envío a "completado"
            $sqlUpdateEnvio = "UPDATE envios SET estado = 'completado' WHERE id = :id AND usuario_id = :usuario_id";
            $stmtEnvio = $this->pdo->prepare($sqlUpdateEnvio);
            $stmtEnvio->execute(['id' => $envio_id, 'usuario_id' => $usuario_id]);
    
            // Obtener los pallets asociados a este envío
            $sqlPallets = "
                SELECT pallet_id, cantidad
                FROM remision_pallets
                WHERE envio_id = :envio_id AND tipo = 'envio'
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
     * Eliminar un envío
     *
     * @param int $envio_id
     * @param int $usuario_id
     * @return int Número de filas afectadas
     */
    public function eliminarEnvio($envio_id, $usuario_id) {
        $sql = "DELETE FROM envios WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $envio_id, 'usuario_id' => $usuario_id]);
        return $stmt->rowCount();
    }

    /**
     * Obtener detalles de un envío específico
     *
     * @param int $envio_id
     * @param int $usuario_id
     * @return array|false
     */
    public function obtenerDetalleEnvio($envio_id, $usuario_id) {
        $sql = " 
            SELECT 
                e.id AS envio_id,
                e.conductor,
                e.placa,
                e.fecha_inicio,
                e.fecha_fin,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                rp.pallet_id,
                rp.cantidad,
                p.tamano AS pallet_tamano,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre
            FROM envios e
            JOIN remision rm ON e.remision_id = rm.id
            JOIN clientes c ON e.cliente_id = c.id
            JOIN transporte t ON e.transporte_id = t.id
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN remision_pallets rp ON e.remision_id = rp.remision_id AND rp.tipo = 'envio'
            LEFT JOIN pallets p ON rp.pallet_id = p.id
            LEFT JOIN titular tit ON e.titular_id = tit.id
            WHERE 
                e.id = :id 
                AND e.usuario_id = :usuario_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $envio_id, 'usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener detalles de una recepción específica
     *
     * @param int $recibir_id
     * @param int $usuario_id
     * @return array|false
     */
    public function obtenerDetalleRecibir($recibir_id, $usuario_id) {
        $sql = " 
            SELECT 
                rcv.id AS recibir_id,
                rcv.conductor,
                rcv.placa,
                rcv.fecha AS fecha,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                rp.pallet_id,
                rp.cantidad,
                p.tamano AS pallet_tamano,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre
            FROM recibir rcv
            JOIN remision rm ON rcv.remision_id = rm.id
            JOIN clientes c ON rcv.cliente_id = c.id
            JOIN transporte t ON rcv.transporte_id = t.id
            LEFT JOIN usuarios u ON rcv.usuario_id = u.id
            LEFT JOIN remision_pallets rp ON rcv.remision_id = rp.remision_id AND rp.tipo = 'recibir'
            LEFT JOIN pallets p ON rp.pallet_id = p.id
            LEFT JOIN titular tit ON rcv.titular_id = tit.id
            WHERE 
                rcv.id = :id 
                AND rcv.usuario_id = :usuario_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $recibir_id, 'usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>