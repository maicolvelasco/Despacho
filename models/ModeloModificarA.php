<?php
// models/ModeloModificar.php

class ModeloModificar {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Obtener detalles del envío por ID
    public function getEnvioById($envio_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                e.*, 
                r.numero AS remision_numero,
                e.fecha_inicio,
                e.fecha_fin,
                u.nombre AS usuario_nombre,
                t.nombre AS transporte_nombre,
                c.nombre AS cliente_nombre,
                tit.nombre AS titular_nombre
            FROM envios e
            JOIN remision r ON e.remision_id = r.id
            LEFT JOIN clientes c ON e.cliente_id = c.id
            LEFT JOIN titular tit ON e.titular_id = tit.id
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN transporte t ON e.transporte_id = t.id
            WHERE e.id = :envio_id
        ");
        $stmt->execute(['envio_id' => $envio_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener pallets asociados al envío
    public function getPalletsAsociados($envio_id) {
        $stmt = $this->pdo->prepare("
            SELECT rp.pallet_id, rp.cantidad, p.tamano 
            FROM remision_pallets rp
            JOIN pallets p ON rp.pallet_id = p.id
            WHERE rp.envio_id = :envio_id AND rp.tipo = 'envio'
        ");
        $stmt->execute(['envio_id' => $envio_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener imágenes asociadas al envío
    public function getImagenesEnvio($envio_id) {
        $stmt = $this->pdo->prepare("
            SELECT imagen FROM envios_imagenes WHERE envio_id = :envio_id
        ");
        $stmt->execute(['envio_id' => $envio_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Obtener todos los clientes
    public function getClientes() {
        $stmt = $this->pdo->query("SELECT id, nombre FROM clientes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los titulares
    public function getTitulares() {
        $stmt = $this->pdo->query("SELECT id, nombre FROM titular");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los transportes
    public function getTransportes() {
        $stmt = $this->pdo->query("SELECT id, nombre FROM transporte");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener usuarios con rol Vendedor (rol_id = 2)
    public function getUsuariosVendedor() {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.nombre 
            FROM usuarios u
            WHERE u.rol_id = 2
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los pallets disponibles
    public function getPallets() {
        $stmt = $this->pdo->query("SELECT id, tamano, stock FROM pallets");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar envío (solo tipo_envio)
    public function actualizarEnvio($data) {
        // Obtener y sanitizar los datos
        $envio_id = $data['envio_id'];
        $tipo_envio = $data['tipo_envio']; // 'propio' o 'duratranz'
        $pallets = $data['pallets'] ?? [];
        $imagenes = $data['captured_images'] ?? [];
        $descripcion_imagen = $data['descripcion_imagen'] ?? [];

        // Validar campos obligatorios
        if (empty($tipo_envio)) {
            throw new Exception("Por favor, selecciona el tipo de envío.");
        }

        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            // Actualizar solo el tipo_envio en el envío
            $stmtEnvio = $this->pdo->prepare("
                UPDATE envios 
                SET 
                    tipo = :tipo_envio
                WHERE id = :envio_id
            ");
            $stmtEnvio->execute([
                'tipo_envio' => $tipo_envio,
                'envio_id' => $envio_id
            ]);

            // Manejar pallets asociados (similar a la lógica anterior)
            // Obtener pallets actuales
            $pallets_asociados_actuales = $this->getPalletsAsociados($envio_id);
            $pallets_asociados_actuales_map = [];
            foreach ($pallets_asociados_actuales as $pallet) {
                $pallets_asociados_actuales_map[$pallet['pallet_id']] = $pallet['cantidad'];
            }

            // Procesar pallets del formulario
            $pallets_nuevos_map = [];
            foreach ($pallets as $pallet) {
                if (isset($pallet['pallet_id']) && isset($pallet['cantidad']) && !empty($pallet['pallet_id']) && !empty($pallet['cantidad'])) {
                    $pallets_nuevos_map[$pallet['pallet_id']] = $pallet['cantidad'];
                }
            }

            // Actualizar la información de pallets en remision_pallets
            $stmtUpdateRemisionPallets = $this->pdo->prepare("
                UPDATE remision_pallets 
                SET cantidad = :cantidad 
                WHERE envio_id = :envio_id AND pallet_id = :pallet_id AND tipo = 'envio'
            ");

            // Ajustar cantidad de pallets
            foreach ($pallets_asociados_actuales_map as $pallet_id => $cantidad_actual) {
                if (isset($pallets_nuevos_map[$pallet_id])) {
                    $cantidad_nueva = $pallets_nuevos_map[$pallet_id];

                    // Actualizar la cantidad en remision_pallets
                    $stmtUpdateRemisionPallets->execute([
                        'cantidad' => $cantidad_nueva,
                        'envio_id' => $envio_id,
                        'pallet_id' => $pallet_id
                    ]);

                    // Remover de los nuevos pallets para manejar solo los nuevos
                    unset($pallets_nuevos_map[$pallet_id]);
                } else {
                    // Pallet eliminado
                    // Eliminar de remision_pallets
                    $stmtDeleteRemisionPallet = $this->pdo->prepare("
                        DELETE FROM remision_pallets 
                        WHERE envio_id = :envio_id AND pallet_id = :pallet_id AND tipo = 'envio'
                    ");
                    $stmtDeleteRemisionPallet->execute(['envio_id' => $envio_id, 'pallet_id' => $pallet_id]);
                }
            }

            // Agregar nuevos pallets
            foreach ($pallets_nuevos_map as $pallet_id => $cantidad) {
                $stmtInsertRemisionPallet = $this->pdo->prepare("
                    INSERT INTO remision_ pallets (envio_id, pallet_id, cantidad, tipo)
                    VALUES (:envio_id, :pallet_id, :cantidad, 'envio')
                ");
                $stmtInsertRemisionPallet->execute([
                    'envio_id' => $envio_id,
                    'pallet_id' => $pallet_id,
                    'cantidad' => $cantidad
                ]);
            }

            // Manejar imágenes: Primero eliminar las existentes y luego insertar las nuevas
            // Alternativamente, podrías implementar lógica para añadir/quitar imágenes específicas

            // Eliminar imágenes existentes
            $stmtDeleteImagenes = $this->pdo->prepare("DELETE FROM envios_imagenes WHERE envio_id = :envio_id");
            $stmtDeleteImagenes->execute(['envio_id' => $envio_id]);

            // Insertar nuevas imágenes
            if (!empty($imagenes) && is_array($imagenes)) {
                foreach ($imagenes as $index => $imagen_base64) {
                    // Decodificar la imagen
                    $imagen_decodificada = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen_base64));

                    // Opcional: Validar el tamaño o tipo de la imagen aquí

                    // Insertar la imagen en la base de datos
                    $stmtImagenes = $this->pdo->prepare("
                        INSERT INTO envios_imagenes (envio_id, imagen, descripcion)
                        VALUES (:envio_id, :imagen, :descripcion)
                    ");
                    $stmtImagenes->execute([
                        'envio_id' => $envio_id,
                        'imagen' => $imagen_decodificada,
                        'descripcion' => isset($descripcion_imagen[$index]) ? trim($descripcion_imagen[$index]) : ''
                    ]);
                }
            }

            // Confirmar transacción
            $this->pdo->commit();
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->pdo->rollBack();
            throw $e; // Re-lanzar la excepción para que el controlador la maneje
        }
    }
}
?>