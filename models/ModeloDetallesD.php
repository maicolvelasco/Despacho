<?php
// /models/ModeloDetalleV.php

class ModeloDetalleD {
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

            // 1. Obtener el usuario_id asociado al registro
            if ($tipo === 'envio') {
                $sql_usuario = "SELECT usuario_id FROM envios WHERE id = :id LIMIT 1";
            } else { // 'recibir'
                $sql_usuario = "SELECT usuario_id FROM recibir WHERE id = :id LIMIT 1";
            }

            $stmt_usuario = $this->pdo->prepare($sql_usuario);
            $stmt_usuario->execute(['id' => $id]);
            $result_usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

            if (!$result_usuario) {
                throw new Exception("No se encontró el registro de tipo '{$tipo}' con ID {$id}.");
            }

            $vendedor_id = intval($result_usuario['usuario_id']);

            // 2. Obtener el rol_id y departamento_id del vendedor
            $sql_vendedor = "SELECT rol_id, departamento_id FROM usuarios WHERE id = :vendedor_id LIMIT 1";
            $stmt_vendedor = $this->pdo->prepare($sql_vendedor);
            $stmt_vendedor->execute(['vendedor_id' => $vendedor_id]);
            $result_vendedor = $stmt_vendedor->fetch(PDO::FETCH_ASSOC);

            if (!$result_vendedor) {
                throw new Exception("No se encontró el Vendedor con ID {$vendedor_id}.");
            }

            $vendedor_rol_id = intval($result_vendedor['rol_id']);
            $vendedor_departamento_id = intval($result_vendedor['departamento_id']);

            foreach ($pallets as $pallet) {
                $pallet_id = $pallet['id'];
                $nueva_cantidad = intval($pallet['cantidad']);

                // 3. Obtener la cantidad original
                if ($tipo === 'envio') {
                    $sql_original = "SELECT cantidad FROM remision_pallets WHERE envio_id = :id AND pallet_id = :pallet_id AND tipo = 'envio' LIMIT 1";
                } else { // 'recibir'
                    $sql_original = "SELECT cantidad FROM remision_pallets WHERE recibir_id = :id AND pallet_id = :pallet_id AND tipo = 'recibir' LIMIT 1";
                }

                $stmt_original = $this->pdo->prepare($sql_original);
                $stmt_original->execute([
                    'id' => $id,
                    'pallet_id' => $pallet_id
                ]);
                $result_original = $stmt_original->fetch(PDO::FETCH_ASSOC);

                if (!$result_original) {
                    throw new Exception("Pallet ID {$pallet_id} no encontrado en la remisión.");
                }

                $cantidad_original = intval($result_original['cantidad']);
                $diferencia = $nueva_cantidad - $cantidad_original;

                // 4. Actualizar la cantidad en remision_pallets
                if ($tipo === 'envio') {
                    $sql_update = "UPDATE remision_pallets SET cantidad = :cantidad WHERE envio_id = :id AND pallet_id = :pallet_id AND tipo = 'envio'";
                } else { // 'recibir'
                    $sql_update = "UPDATE remision_pallets SET cantidad = :cantidad WHERE recibir_id = :id AND pallet_id = :pallet_id AND tipo = 'recibir'";
                }

                $stmt_update = $this->pdo->prepare($sql_update);
                $stmt_update->execute([
                    'cantidad' => $nueva_cantidad,
                    'id' => $id,
                    'pallet_id' => $pallet_id
                ]);

                // 5. Actualizar el stock en la tabla pallets
                $sql_stock = "SELECT stock, departamento_id, tamano FROM pallets WHERE id = :pallet_id LIMIT 1";
                $stmt_stock = $this->pdo->prepare($sql_stock);
                $stmt_stock->execute(['pallet_id' => $pallet_id]);
                $result_stock = $stmt_stock->fetch(PDO::FETCH_ASSOC);

                if (!$result_stock) {
                    throw new Exception("Pallet ID {$pallet_id} no encontrado en la tabla pallets.");
                }

                $stock_actual = intval($result_stock['stock']);
                $pallet_departamento_id = intval($result_stock['departamento_id']);
                $tamano_pallet = $result_stock['tamano'];

                // Calcular el nuevo stock basado en el tipo
                if ($tipo === 'envio') {
                    $nuevo_stock = $stock_actual - $nueva_cantidad;
                } else { // 'recibir'
                    $nuevo_stock = $stock_actual + $nueva_cantidad;
                }

                // Verificar que el nuevo stock no sea negativo
                if ($nuevo_stock < 0) {
                    throw new Exception("Stock insuficiente para el pallet ID {$pallet_id}. Intentaste reducir más de lo disponible.");
                }

                // Actualizar el stock en la tabla pallets
                $sql_update_stock = "UPDATE pallets SET stock = :nuevo_stock WHERE id = :pallet_id";
                $stmt_update_stock = $this->pdo->prepare($sql_update_stock);
                $stmt_update_stock->execute([
                    'nuevo_stock' => $nuevo_stock,
                    'pallet_id' => $pallet_id
                ]);

                // 6. Lógica adicional para ajustar el stock del departamento si el Vendedor tiene rol 4
                if ($vendedor_rol_id == 4) { // Solo para rol 4
                    $sql_super_pallet = "SELECT id, stock FROM pallets WHERE tamano = :tamano AND departamento_id = :departamento_id LIMIT 1";
                    $stmt_super_pallet = $this->pdo->prepare($sql_super_pallet);
                    $stmt_super_pallet->execute([
                        'tamano' => $tamano_pallet,
                        'departamento_id' => $vendedor_departamento_id
                    ]);
                    $super_pallet = $stmt_super_pallet->fetch(PDO::FETCH_ASSOC);

                    if (!$super_pallet) {
                        throw new Exception("No se encontró el pallet correspondiente en el departamento del Supervendedor.");
                    }

                    $super_pallet_id = intval($super_pallet['id']);
                    $super_pallet_stock = intval($super_pallet['stock']);

                    // Ajustar el stock del Supervendedor
                    if ($tipo === 'envio') {
                        $nuevo_stock_super = $super_pallet_stock + $nueva_cantidad;
                    } else { // 'recibir'
                        $nuevo_stock_super = $super_pallet_stock - $nueva_cantidad;
                    }

                    // Verificar que el nuevo stock del Supervendedor no sea negativo
                    if ($nuevo_stock_super < 0) {
                        throw new Exception("Stock insuficiente en el departamento del Supervendedor para el pallet ID {$super_pallet_id}.");
                    }

                    // Actualizar stock del departamento del Supervendedor
                    $sql_update_super_stock = "UPDATE pallets SET stock = :nuevo_stock_super WHERE id = :super_pallet_id";
                    $stmt_update_super_stock = $this->pdo->prepare($sql_update_super_stock);
                    $stmt_update_super_stock->execute([
                        'nuevo_stock_super' => $nuevo_stock_super,
                        'super_pallet_id' => $super_pallet_id
                    ]);
                }
            }

            // 7. Actualizar observación y estado
            if ($tipo === 'envio') {
                $sql = "UPDATE envios SET observacion = :observacion, estado = 'completado' WHERE id = :id";
            } else { // 'recibir'
                $sql = "UPDATE recibir SET observacion = :observacion, estado = 'recibido' WHERE id = :id";
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
            error_log("Error en actualizarPalletsYEstado: " . $e->getMessage());
            return false;
        }
    }
}
?>