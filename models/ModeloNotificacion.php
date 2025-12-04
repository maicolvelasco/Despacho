<?php
// models/ModeloNotificacion.php

class ModeloNotificacion {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtener notificaciones donde modificado = 'no'
     */
public function obtenerNotificacionesNoModificado() {
    $sql = "
        SELECT 
            e.id AS envio_id,
            r.numero AS remision_numero,
            c.nombre AS cliente_nombre,
            tit.nombre AS titular_nombre,
            e.observacion,
            e.fecha_inicio,
            e.fecha_fin,
            -- Calcular la cantidad total enviada
            COALESCE((
                SELECT SUM(rp_envio.cantidad)
                FROM remision_pallets rp_envio
                WHERE rp_envio.envio_id = e.id AND rp_envio.tipo = 'envio'
            ), 0) AS total_enviado,
            -- Calcular la cantidad total recibida
            COALESCE((
                SELECT SUM(rp_recibir.cantidad)
                FROM remision_pallets rp_recibir
                WHERE rp_recibir.remision_id = r.id AND rp_recibir.tipo = 'recibir'
            ), 0) AS total_recibido,
            -- Calcular la cantidad restante
            (
                COALESCE((
                    SELECT SUM(rp_envio.cantidad)
                    FROM remision_pallets rp_envio
                    WHERE rp_envio.envio_id = e.id AND rp_envio.tipo = 'envio'
                ), 0) - COALESCE((
                    SELECT SUM(rp_recibir.cantidad)
                    FROM remision_pallets rp_recibir
                    WHERE rp_recibir.remision_id = r.id AND rp_recibir.tipo = 'recibir'
                ), 0)
            ) AS cantidad_pallets,
            CASE 
                WHEN e.fecha_fin <= CURDATE() THEN 'Caduc車 el tiempo de retenci車n'
                ELSE NULL 
            END AS mensaje_advertencia,
            CASE 
                WHEN e.fecha_fin <= CURDATE() THEN 'si'
                ELSE 'no' 
            END AS es_caducado
        FROM 
            envios e
        JOIN 
            remision r ON e.remision_id = r.id
        LEFT JOIN 
            clientes c ON e.cliente_id = c.id
        LEFT JOIN 
            titular tit ON e.titular_id = tit.id
        WHERE 
            (e.observacion IS NOT NULL AND e.modificado = 'no')
            OR (e.fecha_fin <= CURDATE() AND e.estado_caducidad = 'no')
        HAVING
            cantidad_pallets > 0
        ORDER BY 
            e.fecha_inicio DESC";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Obtener notificaciones donde modificado = 'si'
     */
    public function obtenerNotificacionesModificado() {
        $sql = "
            SELECT 
                e.id AS envio_id,
                r.numero AS remision_numero,
                c.nombre AS cliente_nombre,
                tit.nombre AS titular_nombre,
                e.observacion,
                rp.cantidad AS cantidad_pallets
            FROM 
                envios e
            JOIN 
                remision r ON e.remision_id = r.id
            LEFT JOIN 
                clientes c ON e.cliente_id = c.id
            LEFT JOIN 
                titular tit ON e.titular_id = tit.id
            LEFT JOIN 
                remision_pallets rp ON rp.envio_id = e.id
            WHERE 
                e.observacion IS NOT NULL
                AND e.modificado = 'si'
            ORDER BY 
                e.fecha_inicio DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarModificado($envio_id) {
        try {
            $sql = "UPDATE envios SET modificado = 'si' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $envio_id]);
            $affectedRows = $stmt->rowCount();
            error_log("Actualizar modificado: Filas afectadas para ID $envio_id: $affectedRows");
            return $affectedRows;
        } catch (PDOException $e) {
            error_log("Error en actualizarModificado: " . $e->getMessage());
            return 0;
        }
    }

public function obtenerNotificacionesCaducadas() {
    $sql = "
        SELECT 
            e.id AS envio_id,
            r.numero AS remision_numero,
            c.nombre AS cliente_nombre,
            tit.nombre AS titular_nombre,
            e.fecha_inicio,
            e.fecha_fin,
            -- Calcular la cantidad total enviada
            COALESCE((
                SELECT SUM(rp_envio.cantidad)
                FROM remision_pallets rp_envio
                WHERE rp_envio.envio_id = e.id AND rp_envio.tipo = 'envio'
            ), 0) AS total_enviado,
            -- Calcular la cantidad total recibida
            COALESCE((
                SELECT SUM(rp_recibir.cantidad)
                FROM remision_pallets rp_recibir
                WHERE rp_recibir.remision_id = r.id AND rp_recibir.tipo = 'recibir'
            ), 0) AS total_recibido,
            -- Calcular la cantidad restante
            (
                COALESCE((
                    SELECT SUM(rp_envio.cantidad)
                    FROM remision_pallets rp_envio
                    WHERE rp_envio.envio_id = e.id AND rp_envio.tipo = 'envio'
                ), 0) - COALESCE((
                    SELECT SUM(rp_recibir.cantidad)
                    FROM remision_pallets rp_recibir
                    WHERE rp_recibir.remision_id = r.id AND rp_recibir.tipo = 'recibir'
                ), 0)
            ) AS cantidad_pallets,
            'Caduc車 el tiempo de retenci車n' AS mensaje_advertencia
        FROM 
            envios e
        JOIN 
            remision r ON e.remision_id = r.id
        LEFT JOIN 
            clientes c ON e.cliente_id = c.id
        LEFT JOIN 
            titular tit ON e.titular_id = tit.id
        WHERE 
            e.estado_caducidad = 'si'
        HAVING
            cantidad_pallets > 0
        ORDER BY 
            e.fecha_fin DESC";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function actualizarEstadoCaducidad($envio_id) {
        try {
            $sql = "UPDATE envios SET estado_caducidad = 'si' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $envio_id]);
            $affectedRows = $stmt->rowCount();
            error_log("Actualizar estado de caducidad: Filas afectadas para ID $envio_id: $affectedRows");
            return $affectedRows;
        } catch (PDOException $e) {
            error_log("Error en actualizarEstadoCaducidad: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar las notificaciones donde modificado = 'no' y observacion no es NULL
     */
public function contarNotificacionesNoModificado() {
    $sql = "
        SELECT 
            COUNT(*) AS total
        FROM (
            SELECT 
                e.id AS envio_id,
                -- Calcular la cantidad restante de pallets
                (
                    COALESCE((
                        SELECT SUM(rp_envio.cantidad)
                        FROM remision_pallets rp_envio
                        WHERE rp_envio.envio_id = e.id AND rp_envio.tipo = 'envio'
                    ), 0) - COALESCE((
                        SELECT SUM(rp_recibir.cantidad)
                        FROM remision_pallets rp_recibir
                        WHERE rp_recibir.remision_id = r.id AND rp_recibir.tipo = 'recibir'
                    ), 0)
                ) AS cantidad_pallets
            FROM 
                envios e
            JOIN 
                remision r ON e.remision_id = r.id
            WHERE 
                e.observacion IS NOT NULL AND e.modificado = 'no'
        ) AS subquery
        WHERE cantidad_pallets > 0";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

public function contarNotificacionesCaducadas() {
    $sql = "
        SELECT 
            COUNT(*) AS total
        FROM (
            SELECT 
                e.id AS envio_id,
                -- Calcular la cantidad restante de pallets
                (
                    COALESCE((
                        SELECT SUM(rp_envio.cantidad)
                        FROM remision_pallets rp_envio
                        WHERE rp_envio.envio_id = e.id AND rp_envio.tipo = 'envio'
                    ), 0) - COALESCE((
                        SELECT SUM(rp_recibir.cantidad)
                        FROM remision_pallets rp_recibir
                        WHERE rp_recibir.remision_id = r.id AND rp_recibir.tipo = 'recibir'
                    ), 0)
                ) AS cantidad_pallets
            FROM 
                envios e
            JOIN 
                remision r ON e.remision_id = r.id
            WHERE 
                e.estado_caducidad = 'no' 
                AND e.fecha_fin <= CURDATE()
        ) AS subquery
        WHERE cantidad_pallets > 0";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

}
?>