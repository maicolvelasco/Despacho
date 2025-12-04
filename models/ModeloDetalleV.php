<?php
// /models/ModeloDetalleV.php

class ModeloDetalleV {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtener detalles por ID y tipo, incluyendo titular y cliente.
     *
     * @param int $id
     * @param string $tipo ('envio' o 'recibir')
     * @return array|false
     */
    public function getDetalleById($id, $tipo) {
        if ($tipo === 'envio') {
            $sql = "
                SELECT 
                    e.id AS envio_id,
                    e.conductor,
                    e.placa,
                    e.fecha_inicio,
                    e.fecha_fin,
                    e.tipo AS tipo_envio,
                    r.numero AS remision_numero,
                    c.nombre AS cliente_nombre,
                    t.nombre AS transporte_nombre,
                    u.nombre AS usuario_nombre,
                    tit.nombre AS titular_nombre,
                    e.estado,
                    e.observacion
                FROM 
                    envios e
                JOIN 
                    remision r ON e.remision_id = r.id
                LEFT JOIN 
                    clientes c ON e.cliente_id = c.id
                LEFT JOIN 
                    titular tit ON e.titular_id = tit.id
                JOIN 
                    transporte t ON e.transporte_id = t.id
                LEFT JOIN 
                    usuarios u ON e.usuario_id = u.id
                WHERE 
                    e.id = :id
                LIMIT 1
            ";
        } else { // recibir
            $sql = "
                SELECT 
                    rcv.id AS recibir_id,
                    rcv.conductor,
                    rcv.placa,
                    rcv.fecha,
                    rcv.tipo AS tipo_recibir,
                    r.numero AS remision_numero,
                    c.nombre AS cliente_nombre,
                    t.nombre AS transporte_nombre,
                    u.nombre AS usuario_nombre,
                    tit.nombre AS titular_nombre,
                    rcv.estado,
                    rcv.observacion
                FROM 
                    recibir rcv
                JOIN 
                    remision r ON rcv.remision_id = r.id
                LEFT JOIN 
                    clientes c ON rcv.cliente_id = c.id
                LEFT JOIN 
                    titular tit ON rcv.titular_id = tit.id
                JOIN 
                    transporte t ON rcv.transporte_id = t.id
                LEFT JOIN 
                    usuarios u ON rcv.usuario_id = u.id
                WHERE 
                    rcv.id = :id
                LIMIT 1
            ";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener pallets asociados por ID y tipo.
     *
     * @param int $id
     * @param string $tipo ('envio' o 'recibir')
     * @return array
     */
    public function getPalletsById($id, $tipo) {
        if ($tipo === 'envio') {
            $sql = "
                SELECT 
                    rp.id AS remision_pallet_id,
                    p.id AS pallet_id,
                    p.tamano,
                    rp.cantidad
                FROM 
                    remision_pallets rp
                JOIN 
                    pallets p ON rp.pallet_id = p.id
                WHERE 
                    rp.envio_id = :id AND rp.tipo = 'envio'
            ";
        } else { // recibir
            $sql = "
                SELECT 
                    rp.id AS remision_pallet_id,
                    p.id AS pallet_id,
                    p.tamano,
                    rp.cantidad
                FROM 
                    remision_pallets rp
                JOIN 
                    pallets p ON rp.pallet_id = p.id
                WHERE 
                    rp.recibir_id = :id AND rp.tipo = 'recibir'
            ";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener imágenes asociadas por ID y tipo, incluyendo descripciones.
     *
     * @param int $detalle_id (envio_id o recibir_id)
     * @param string $tipo ('envio' o 'recibir')
     * @return array
     */
    public function getImagenesByDetalle($detalle_id, $tipo) {
        if ($tipo === 'envio') {
            $sql = "SELECT imagen, descripcion FROM envios_imagenes WHERE envio_id = :detalle_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['detalle_id' => $detalle_id]);
        } else { // recibir
            $sql = "SELECT imagen, descripcion FROM recibir_imagenes WHERE recibir_id = :detalle_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['detalle_id' => $detalle_id]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener la observación de un envio o recibir por ID y tipo.
     *
     * @param int $id
     * @param string $tipo ('envio' o 'recibir')
     * @return string|null
     */
    public function getObservacion($id, $tipo) {
        if ($tipo === 'envio') {
            $sql = "SELECT observacion FROM envios WHERE id = :id LIMIT 1";
        } else { // recibir
            $sql = "SELECT observacion FROM recibir WHERE id = :id LIMIT 1";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['observacion'] : null;
    }

    /**
     * Actualizar los pallets y la observación, y cambiar el estado.
     *
     * @param int $id
     * @param string $tipo ('envio' o 'recibir')
     * @param array $pallets (array de pallets con 'id' y 'cantidad')
     * @param string $observacion
     * @return bool
     */
    public function actualizarPalletsYEstado($id, $tipo, $pallets, $observacion) {
        try {
            $this->pdo->beginTransaction();

            foreach ($pallets as $pallet) {
                $pallet_id = $pallet['id'];
                $cantidad_a_restar = intval($pallet['cantidad']); 

                // Obtener el stock actual del pallet
                $sql_stock = "SELECT stock FROM pallets WHERE id = :pallet_id LIMIT 1";
                $stmt_stock = $this->pdo->prepare($sql_stock);
                $stmt_stock->execute(['pallet_id' => $pallet_id]);
                $result_stock = $stmt_stock->fetch(PDO::FETCH_ASSOC);

                if (!$result_stock) {
                    throw new Exception("Pallet ID {$pallet_id} no encontrado en la tabla pallets.");
                }

                $stock_actual = intval($result_stock['stock']);

                // Verificar que haya suficiente stock
                if ($stock_actual < $cantidad_a_restar) {
                    throw new Exception("Stock insuficiente para el pallet ID {$pallet_id}. Intentaste restar más de lo disponible.");
                }

                // Actualizar el stock en la tabla pallets
                $nuevo_stock = $stock_actual - $cantidad_a_restar;
                $sql_update_stock = "UPDATE pallets SET stock = :nuevo_stock WHERE id = :pallet_id";
                $stmt_update_stock = $this->pdo->prepare($sql_update_stock);
                $stmt_update_stock->execute([
                    'nuevo_stock' => $nuevo_stock,
                    'pallet_id' => $pallet_id
                ]);

                // Actualizar la cantidad en remision_pallets
                if ($tipo === 'envio') {
                    $sql_update_cantidad = "UPDATE remision_pallets SET cantidad = :cantidad WHERE envio_id = :id AND pallet_id = :pallet_id AND tipo = 'envio'";
                } else { // 'recibir'
                    $sql_update_cantidad = "UPDATE remision_pallets SET cantidad = :cantidad WHERE recibir_id = :id AND pallet_id = :pallet_id AND tipo = 'recibir'";
                }

                $stmt_update_cantidad = $this->pdo->prepare($sql_update_cantidad);
                $stmt_update_cantidad->execute([
                    'cantidad' => $cantidad_a_restar, // Actualizar con la cantidad a restar
                    'id' => $id,
                    'pallet_id' => $pallet_id
                ]);
            }

            // Actualizar observación y estado
            if ($tipo === 'envio') {
                $sql = "UPDATE envios SET observacion = :observacion, estado = 'completado' WHERE id = :id";
            } else { // 'recibir'
                $sql = "UPDATE recibir SET observacion = :observacion, estado = 'completado' WHERE id = :id";
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'observacion' => $observacion,
                'id' => $id
            ]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Puedes registrar el error $e->getMessage() en un log para depuración
            return false;
        }
    }
}
?>