<?php
// models/ModeloNotificacionV.php

class ModeloNotificacionV {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtener notificaciones de recibir donde modificado = 'no' para un usuario específico
     */
    public function obtenerNotificacionesNoModificadoV($usuario_id) {
        $sql = "
            SELECT 
                rcv.id AS recibir_id,
                rem.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                tit.nombre AS titular_nombre,
                rcv.observacion
            FROM 
                recibir rcv
            JOIN 
                remision rem ON rcv.remision_id = rem.id
            LEFT JOIN 
                clientes c ON rcv.cliente_id = c.id
            LEFT JOIN 
                titular tit ON rcv.titular_id = tit.id
            WHERE 
                rcv.observacion IS NOT NULL
                AND rcv.modificado = 'no'
                AND rcv.usuario_id = :usuario_id
            ORDER BY 
                rcv.fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener notificaciones de recibir donde modificado = 'si' para un usuario específico
     *
     * @param int $usuario_id
     * @return array
     */
    public function obtenerNotificacionesModificadoV($usuario_id) {
        $sql = "
            SELECT 
                rcv.id AS recibir_id,
                rem.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                tit.nombre AS titular_nombre,
                rcv.observacion
            FROM 
                recibir rcv
            JOIN 
                remision rem ON rcv.remision_id = rem.id
            LEFT JOIN 
                clientes c ON rcv.cliente_id = c.id
            LEFT JOIN 
                titular tit ON rcv.titular_id = tit.id
            WHERE 
                rcv.observacion IS NOT NULL
                AND rcv.modificado = 'si'
                AND rcv.usuario_id = :usuario_id
            ORDER BY 
                rcv.fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar el campo modificado a 'si' para un recibir específico
     *
     * @param int $recibir_id
     * @return int Número de filas afectadas
     */
    public function actualizarModificadoV($recibir_id) {
        $sql = "UPDATE recibir SET modificado = 'si' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $recibir_id]);
        return $stmt->rowCount();
    }

public function obtenerEnviosCaducadosNoModificadoV($usuario_id) {
    $sql = "
        SELECT 
            env.id AS envio_id,
            rem.numero AS remision_numero,
            env.fecha_inicio,
            env.fecha_fin,
            -- Calcular la cantidad total enviada
            COALESCE((
                SELECT SUM(rp_envio.cantidad)
                FROM remision_pallets rp_envio
                WHERE rp_envio.envio_id = env.id AND rp_envio.tipo = 'envio'
            ), 0) AS total_enviado,
            -- Calcular la cantidad total recibida
            COALESCE((
                SELECT SUM(rp_recibir.cantidad)
                FROM remision_pallets rp_recibir
                WHERE rp_recibir.remision_id = rem.id AND rp_recibir.tipo = 'recibir'
            ), 0) AS total_recibido,
            -- Calcular la cantidad restante
            (
                COALESCE((
                    SELECT SUM(rp_envio.cantidad)
                    FROM remision_pallets rp_envio
                    WHERE rp_envio.envio_id = env.id AND rp_envio.tipo = 'envio'
                ), 0) - COALESCE((
                    SELECT SUM(rp_recibir.cantidad)
                    FROM remision_pallets rp_recibir
                    WHERE rp_recibir.remision_id = rem.id AND rp_recibir.tipo = 'recibir'
                ), 0)
            ) AS cantidad_pallets,
            'Caducó el tiempo de retención' AS mensaje
        FROM 
            envios env
        JOIN 
            remision rem ON env.remision_id = rem.id
        WHERE 
            env.fecha_fin <= CURDATE()
            AND env.estado_caducado_vendedor = 'no'
            AND env.usuario_id = :usuario_id
        HAVING
            cantidad_pallets > 0
        ORDER BY 
            env.fecha_fin DESC";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function obtenerEnviosCaducadosV($usuario_id) {
    $sql = "
        SELECT 
            env.id AS envio_id,
            rem.numero AS remision_numero,
            env.fecha_inicio,
            env.fecha_fin,
            -- Calcular la cantidad total enviada
            COALESCE((
                SELECT SUM(rp_envio.cantidad)
                FROM remision_pallets rp_envio
                WHERE rp_envio.envio_id = env.id AND rp_envio.tipo = 'envio'
            ), 0) AS total_enviado,
            -- Calcular la cantidad total recibida
            COALESCE((
                SELECT SUM(rp_recibir.cantidad)
                FROM remision_pallets rp_recibir
                WHERE rp_recibir.remision_id = rem.id AND rp_recibir.tipo = 'recibir'
            ), 0) AS total_recibido,
            -- Calcular la cantidad restante
            (
                COALESCE((
                    SELECT SUM(rp_envio.cantidad)
                    FROM remision_pallets rp_envio
                    WHERE rp_envio.envio_id = env.id AND rp_envio.tipo = 'envio'
                ), 0) - COALESCE((
                    SELECT SUM(rp_recibir.cantidad)
                    FROM remision_pallets rp_recibir
                    WHERE rp_recibir.remision_id = rem.id AND rp_recibir.tipo = 'recibir'
                ), 0)
            ) AS cantidad_pallets,
            'Caducó el tiempo de retención' AS mensaje
        FROM 
            envios env
        JOIN 
            remision rem ON env.remision_id = rem.id
        WHERE 
            env.estado_caducado_vendedor = 'si'
            AND env.usuario_id = :usuario_id
        HAVING
            cantidad_pallets > 0
        ORDER BY 
            env.fecha_fin DESC";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Marcar un envío como caducado
     *
     * @param int $envio_id
     * @return int Número de filas afectadas
     */
    public function actualizarEstadoCaducadoV($envio_id) {
        $sql = "UPDATE envios SET estado_caducado_vendedor = 'si' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $envio_id]);
        return $stmt->rowCount();
    }

    /**
     * Contar las notificaciones de recibir donde modificado = 'no' y observacion no es NULL para un usuario específico
     *
     * @param int $usuario_id
     * @return int
     */
    public function contarNotificacionesNoModificadoV($usuario_id) {
        $sql = "SELECT COUNT(*) AS total FROM recibir WHERE observacion IS NOT NULL AND modificado = 'no' AND usuario_id = :usuario_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Contar los envíos caducados no modificados para un usuario específico
     *
     * @param int $usuario_id
     * @return int
     */
    public function contarEnviosCaducadosNoModificadoV($usuario_id) {
        $sql = "SELECT COUNT(*) AS total FROM envios WHERE fecha_fin <= CURDATE() AND estado_caducado_vendedor = 'no' AND usuario_id = :usuario_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>