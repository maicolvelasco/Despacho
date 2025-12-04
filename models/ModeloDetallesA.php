<?php
// models/ModeloDetalles.php

class ModeloDetalles {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Obtener detalles del envío o recepción
    public function obtenerDetalles($id, $tipo) {
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

    // Obtener pallets asociados al envío o recepción
    public function obtenerPallets($id, $tipo) {
        if ($tipo === 'envio') {
            $sql = "
                SELECT 
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

    // Obtener imágenes asociadas
    public function obtenerImagenes($detalle, $tipo) {
        if ($tipo === 'envio') {
            $sql = "SELECT imagen, descripcion FROM envios_imagenes WHERE envio_id = :envio_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['envio_id' => $detalle['envio_id']]);
        } else { // recibir
            $sql = "SELECT imagen, descripcion FROM recibir_imagenes WHERE recibir_id = :recibir_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['recibir_id' => $detalle['recibir_id']]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

            // Paso 1: Obtener usuario_id, rol_id y departamento_id si es 'recibir'
            if ($tipo === 'recibir') {
                // Obtener usuario_id de la tabla 'recibir'
                $sql_usuario = "SELECT usuario_id FROM recibir WHERE id = :id LIMIT 1";
                $stmt_usuario = $this->pdo->prepare($sql_usuario);
                $stmt_usuario->execute(['id' => $id]);
                $result_usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

                if (!$result_usuario) {
                    throw new Exception("No se encontró el registro de recibir con ID {$id}.");
                }

                $usuario_id = intval($result_usuario['usuario_id']);

                // Obtener rol_id y departamento_id de la tabla 'usuarios'
                $sql_rol_departamento = "SELECT rol_id, departamento_id FROM usuarios WHERE id = :usuario_id LIMIT 1";
                $stmt_rol_departamento = $this->pdo->prepare($sql_rol_departamento);
                $stmt_rol_departamento->execute(['usuario_id' => $usuario_id]);
                $result_rol_departamento = $stmt_rol_departamento->fetch(PDO::FETCH_ASSOC);

                if (!$result_rol_departamento) {
                    throw new Exception("No se encontró el usuario con ID {$usuario_id}.");
                }

                $rol_id = intval($result_rol_departamento['rol_id']);
                $departamento_id_vendedor = intval($result_rol_departamento['departamento_id']);
            } else {
                // Para 'envio', si también deseas aplicar lógica similar, puedes implementarlo aquí
                $rol_id = null;
                $departamento_id_vendedor = null;
            }

            foreach ($pallets as $pallet) {
                $pallet_id = $pallet['id'];
                $nueva_cantidad = intval($pallet['cantidad']);

                // Obtener la cantidad original
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

                // Actualizar la cantidad en remision_pallets
                if ($tipo === 'envio') {
                    $sql_update = "UPDATE remision_pallets SET cantidad = :cantidad WHERE envio_id = :id AND pallet_id = :pallet_id AND tipo = 'envio'";
                } else { // 'recibir'
                    $sql_update = "UPDATE remision_pallets SET cantidad = :cantidad WHERE recibir_id = :id AND pallet_id = :pallet_id AND tipo = 'recibir'";
                }

                $stmt_update = $this->pdo->prepare($sql_update);
                $stmt_update-> execute([
                    'cantidad' => $nueva_cantidad,
                    'id' => $id,
                    'pallet_id' => $pallet_id
                ]);

                // Actualizar el stock en la tabla pallets
                // Obtener el stock actual
                $sql_stock = "SELECT stock FROM pallets WHERE id = :pallet_id LIMIT 1";
                $stmt_stock = $this->pdo->prepare($sql_stock);
                $stmt_stock->execute(['pallet_id' => $pallet_id]);
                $result_stock = $stmt_stock->fetch(PDO::FETCH_ASSOC);

                if (!$result_stock) {
                    throw new Exception("Pallet ID {$pallet_id} no encontrado en la tabla pallets.");
                }

                $stock_actual = intval($result_stock['stock']);

                // Calcular el nuevo stock
                if ($tipo === 'envio') {
                    // Si se aumenta, se suma la cantidad
                    $nuevo_stock = $stock_actual - $nueva_cantidad;
                } else { // 'recibir'
                    // Si se aumenta, se suma la cantidad
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

                // Paso 2: Si es 'recibir' y el usuario es Supervendedor (rol_id = 4), ajustar el pallet del departamento
                if ($tipo === 'recibir' && $rol_id === 4) {
                    // Obtener el 'tamano' del pallet actual
                    $sql_tamano = "SELECT tamano FROM pallets WHERE id = :pallet_id LIMIT 1";
                    $stmt_tamano = $this->pdo->prepare($sql_tamano);
                    $stmt_tamano->execute(['pallet_id' => $pallet_id]);
                    $result_tamano = $stmt_tamano->fetch(PDO::FETCH_ASSOC);

                    if (!$result_tamano) {
                        throw new Exception("No se pudo obtener el tamaño del pallet ID {$pallet_id}.");
                    }

                    $tamano_pallet = $result_tamano['tamano'];

                    // Buscar el pallet del departamento con el mismo tamaño
                    $sql_pallet_departamento = "SELECT id, stock FROM pallets WHERE tamano = :tamano AND departamento_id = :departamento_id LIMIT 1";
                    $stmt_pallet_departamento = $this->pdo->prepare($sql_pallet_departamento);
                    $stmt_pallet_departamento->execute([
                        'tamano' => $tamano_pallet,
                        'departamento_id' => $departamento_id_vendedor
                    ]);
                    $result_pallet_departamento = $stmt_pallet_departamento->fetch(PDO::FETCH_ASSOC);

                    if (!$result_pallet_departamento) {
                        throw new Exception("No se encontró un pallet para el departamento ID {$departamento_id_vendedor} con tamaño '{$tamano_pallet}'.");
                    }

                    $pallet_departamento_id = intval($result_pallet_departamento['id']);
                    $stock_departamento_actual = intval($result_pallet_departamento['stock']);

                    // Calcular el nuevo stock para el pallet del departamento
                    if ($nueva_cantidad > 0) {
                        // Si la cantidad aumenta, restar del departamento
                        $nuevo_stock_departamento = $stock_departamento_actual - $nueva_cantidad;
                        if ($nuevo_stock_departamento < 0) {
                            throw new Exception("Stock insuficiente en el pallet del departamento ID {$pallet_departamento_id} para restar {$nueva_cantidad} unidades.");
                        }

                        $sql_update_stock_departamento = "UPDATE pallets SET stock = :nuevo_stock WHERE id = :pallet_departamento_id";
                        $stmt_update_stock_departamento = $this->pdo->prepare($sql_update_stock_departamento);
                        $stmt_update_stock_departamento->execute([
                            'nuevo_stock' => $nuevo_stock_departamento,
                            'pallet_departamento_id' => $pallet_departamento_id
                        ]);
                    } else {
                        // Si la cantidad disminuye, sumar al departamento
                        $nuevo_stock_departamento = $stock_departamento_actual + abs($nueva_cantidad);

                        $sql_update_stock_departamento = "UPDATE pallets SET stock = :nuevo_stock WHERE id = :pallet_departamento_id";
                        $stmt_update_stock_departamento = $this->pdo->prepare($sql_update_stock_departamento);
                        $stmt_update_stock_departamento->execute([
                            'nuevo_stock' => $nuevo_stock_departamento,
                            'pallet_departamento_id' => $pallet_departamento_id
                        ]);
                    }
                }
            }

            // Actualizar observación y estado
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
            // Puedes registrar el error $e->getMessage() en un log para depuración
            return false;
        }
    }
}
?>