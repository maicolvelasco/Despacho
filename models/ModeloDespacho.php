<?php
// models/ModeloDespacho.php

class ModeloDespacho {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Obtener todos los envíos
    public function obtenerEnvios() {
        $sql = "
            SELECT 
                e.id AS envio_id,
                e.conductor,
                e.placa,
                e.fecha_inicio,
                e.fecha_fin,
                r.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
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
                pallets p ON rp.pallet_id = p.id
            LEFT JOIN 
                departamentos d ON p.departamento_id = d.id
            GROUP BY 
                e.id
            ORDER BY 
                e.fecha_inicio DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerRecibirEnTransito() {
        $sql = "
            SELECT 
                rcv.id AS recibir_id,
                rcv.conductor,
                rcv.placa,
                rcv.fecha,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
                rcv.estado,
                SUM(rp.cantidad) AS total_pallets,
                COALESCE(tit.nombre, 'N/A') AS titular_nombre,
                COALESCE(d.nombre, 'N/A') AS departamento_origen
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
                pallets p ON rp.pallet_id = p.id
            LEFT JOIN 
                departamentos d ON p.departamento_id = d.id
            WHERE 
                rcv.estado = 'en_transito'
            GROUP BY 
                rcv.id
            ORDER BY 
                rcv.fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerRecibirRecibidos() {
        $sql = "
            SELECT 
                rcv.id AS recibir_id,
                rcv.conductor,
                rcv.placa,
                rcv.fecha,
                rm.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                t.nombre AS transporte_nombre,
                u.nombre AS usuario_nombre,
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
                COALESCE(d.nombre, 'N/A') AS departamento_origen
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
                pallets p ON rp.pallet_id = p.id
            LEFT JOIN 
                departamentos d ON p.departamento_id = d.id
            WHERE 
                rcv.estado = 'recibido'
            GROUP BY 
                rcv.id
            ORDER BY 
                rcv.fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Aceptar una recepción (actualizar su estado a 'recibido')
     *
     * @param int $recibir_id
     * @return int Número de filas afectadas
     */
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
    
                // Obtener los pallets asociados a esta recepción con su tamaño
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
    
                            // Sumar los pallets al stock del departamento por tamaño
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
     * Eliminar una recepción
     *
     * @param int $recibir_id
     * @return int Número de filas afectadas
     */
    public function eliminarRecepcion($recibir_id) {
        $sql = "DELETE FROM recibir WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $recibir_id]);
        return $stmt->rowCount();
    }
}
?>