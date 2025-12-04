<?php
    // models/ModeloDevolucion.php 
    
    /**
     * Obtiene las remisiones disponibles para usuarios del mismo departamento que el usuario logueado.
     *
     * @param PDO $pdo Conexión PDO a la base de datos.
     * @param int $usuario_id ID del usuario logueado.
     * @return array Lista de remisiones disponibles con nombre asociado.
     */
    function getRemisionesDisponibles($pdo, $usuario_id) {
        // Obtener el departamento del usuario logueado
        $stmtDept = $pdo->prepare("SELECT departamento_id FROM usuarios WHERE id = :usuario_id");
        $stmtDept->execute(['usuario_id' => $usuario_id]);
        $departamento = $stmtDept->fetchColumn();
    
        if (!$departamento) {
            // Si no se encuentra el departamento, retornar un array vacío
            return [];
        }
    
        // Obtener remisiones asociadas a cualquier usuario del mismo departamento que aún tienen pallets pendientes
        $stmt = $pdo->prepare("
            SELECT DISTINCT r.id, r.numero, c.nombre AS cliente_nombre, tit.nombre AS titular_nombre
            FROM remision r
            INNER JOIN envios e ON e.remision_id = r.id
            INNER JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN clientes c ON c.id = e.cliente_id
            LEFT JOIN titular tit ON tit.id = e.titular_id
            WHERE u.departamento_id = :departamento_id
        ");
        $stmt->execute(['departamento_id' => $departamento]);
        $remisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Filtrar remisiones que aún tienen pallets pendientes
        $remisionesDisponibles = [];
        foreach ($remisiones as $remision) {
            $palletsRemision = getUltimoRegistroRemision($pdo, $remision['id']);
            if (!empty($palletsRemision)) {
                // Decidir qué nombre mostrar: cliente_nombre o titular_nombre
                $nombre_a_mostrar = !empty($remision['cliente_nombre']) ? $remision['cliente_nombre'] : $remision['titular_nombre'];
    
                // Agregar a remisiones disponibles con el nombre
                $remisionesDisponibles[] = [
                    'id' => $remision['id'],
                    'numero' => $remision['numero'],
                    'nombre' => $nombre_a_mostrar
                ];
            }
        }
    
        return $remisionesDisponibles;
    }

    function getUltimoRegistroRemision($pdo, $remision_id) {
        // Obtener el estado actual de los pallets para la remisión, incluyendo el departamento
        $stmt = $pdo->prepare("
            SELECT 
                rp.pallet_id, 
                p.tamano, 
                SUM(CASE WHEN rp.tipo = 'envio' THEN rp.cantidad ELSE -rp.cantidad END) as cantidad, 
                d.nombre as departamento_nombre,
                d.id as departamento_id
            FROM remision_pallets rp
            INNER JOIN pallets p ON p.id = rp.pallet_id
            LEFT JOIN departamentos d ON p.departamento_id = d.id
            WHERE rp.remision_id = :remision_id
            GROUP BY rp.pallet_id, p.tamano, d.nombre, d.id
            HAVING cantidad > 0
        ");
        $stmt->execute(['remision_id' => $remision_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    /**
     * Obtiene cliente, vendedor, transporte, titular, rol y departamento asociados a una remisión.
     *
     * @param PDO $pdo Conexión PDO a la base de datos.
     * @param int $remision_id ID de la remisión.
     * @return array|false Datos del cliente, vendedor, rol y departamento o false si no se encuentran.
     */
    function getClienteYVendedorPorRemision($pdo, $remision_id) {
        // Obtener cliente, vendedor, transporte, titular, rol_id y departamento_id asociados a la remisión
        $stmt = $pdo->prepare("
            SELECT 
                c.id as cliente_id, 
                c.nombre as cliente_nombre,
                u.id as vendedor_id, 
                u.nombre as vendedor_nombre,
                t.id as transporte_id, 
                t.nombre as transporte_nombre,
                tit.id as titular_id, 
                tit.nombre as titular_nombre,
                r.id as rol_id,
                u.departamento_id as remision_departamento_id
            FROM envios e
            LEFT JOIN clientes c ON c.id = e.cliente_id
            INNER JOIN usuarios u ON u.id = e.usuario_id
            INNER JOIN transporte t ON t.id = e.transporte_id
            LEFT JOIN titular tit ON tit.id = e.titular_id
            LEFT JOIN roles r ON u.rol_id = r.id
            WHERE e.remision_id = :remision_id
            ORDER BY e.id DESC 
            LIMIT 1
        ");
        $stmt->execute(['remision_id' => $remision_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
    /**
     * Obtiene la lista de transportes disponibles.
     *
     * @param PDO $pdo Conexión PDO a la base de datos.
     * @return array Lista de transportes.
     */
    function getTransportes($pdo) {
        $stmt = $pdo->query("SELECT id, nombre FROM transporte");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
function insertarRecibir($pdo, $remision_id, $conductor, $placa, $cliente_id, $usuario_id, $transporte_id, $titular_id, $imagenes, $descripcion_imagen, $pallets, $tipo) {
    $fecha = date('Y-m-d');

    // Insertar en la tabla 'recibir' incluyendo el campo 'tipo'
    $stmtRecibir = $pdo->prepare("
        INSERT INTO recibir (conductor, placa, fecha, remision_id, cliente_id, usuario_id, transporte_id, titular_id, tipo, estado)
        VALUES (:conductor, :placa, :fecha, :remision_id, :cliente_id, :usuario_id, :transporte_id, :titular_id, :tipo, 'en_transito')
    ");
    $stmtRecibir->execute([
        'conductor' => $conductor,
        'placa' => $placa,
        'fecha' => $fecha,
        'remision_id' => $remision_id,
        'cliente_id' => $cliente_id ?: NULL, // Puede ser NULL
        'usuario_id' => $usuario_id,
        'transporte_id' => $transporte_id,
        'titular_id' => $titular_id ?: NULL,
        'tipo' => $tipo
    ]);
    $recibir_id = $pdo->lastInsertId();

// Insertar pallets sin actualizar el stock
if (!empty($pallets) && is_array($pallets)) {
    foreach ($pallets as $pallet) {
        if (isset($pallet['pallet_id']) && isset($pallet['cantidad'])) {
            // Validar que la cantidad no exceda la cantidad actual
            $stmtCantidadActual = $pdo->prepare("
                SELECT SUM(CASE WHEN tipo = 'envio' THEN cantidad ELSE -cantidad END) as cantidad_actual
                FROM remision_pallets
                WHERE remision_id = :remision_id AND pallet_id = :pallet_id
                GROUP BY pallet_id
            ");
            $stmtCantidadActual->execute([
                'remision_id' => $remision_id,
                'pallet_id' => $pallet['pallet_id']
            ]);
            $cantidadActual = $stmtCantidadActual->fetchColumn();

            if ($pallet['cantidad'] > $cantidadActual) {
                throw new Exception("La cantidad ingresada para el pallet ID {$pallet['pallet_id']} excede la cantidad actual ({$cantidadActual}).");
            }

            // Solo insertar el pallet, pero no actualizar el stock
            $stmtPallets = $pdo->prepare("
                INSERT INTO remision_pallets (remision_id, recibir_id, pallet_id, cantidad, tipo)
                VALUES (:remision_id, :recibir_id, :pallet_id, :cantidad, 'recibir')
            ");
            $stmtPallets->execute([
                'remision_id' => $remision_id,
                'recibir_id' => $recibir_id,
                'pallet_id' => $pallet['pallet_id'],
                'cantidad' => $pallet['cantidad']
            ]);
        }
    }
}

    // Insertar imágenes con compresión
    if (!empty($imagenes) && is_array($imagenes)) {
        foreach ($imagenes as $index => $imagen_base64) {
            // Eliminar prefijo de datos base64 si existe
            $imagen_base64 = preg_replace('#^data:image/\w+;base64,#i', '', $imagen_base64);
            
            // Decodificar la imagen
            $imagen_decodificada = base64_decode($imagen_base64);
            
            // Verificar que la imagen se decodificó correctamente
            if ($imagen_decodificada === false) {
                // Saltar esta imagen si no se puede decodificar
                continue;
            }

            // Comprimir la imagen
            $imagen_comprimida = comprimirImagen($imagen_decodificada);

            // Preparar la descripción
            $descripcion = !empty($descripcion_imagen[$index]) ? 
                trim($descripcion_imagen[$index]) : 
                "Imagen " . ($index + 1);

            // Insertar imagen
            $stmtImagenes = $pdo->prepare("
                INSERT INTO recibir_imagenes (recibir_id, imagen, descripcion)
                VALUES (:recibir_id, :imagen, :descripcion)
            ");
            
            $stmtImagenes->execute([
                'recibir_id' => $recibir_id,
                'imagen' => $imagen_comprimida,
                'descripcion' => $descripcion
            ]);
        }
    }

    return $recibir_id;
}

// Función para comprimir imagen
function comprimirImagen($imagen_original, $calidad = 75, $max_tamano = 1024 * 1024) {
    // Si la imagen es pequeña, devolverla sin comprimir
    if (strlen($imagen_original) <= $max_tamano) {
        return $imagen_original;
    }

    // Crear imagen desde los datos
    $imagen = imagecreatefromstring($imagen_original);
    
    if ($imagen === false) {
        return $imagen_original;
    }

    // Obtener dimensiones originales
    $ancho = imagesx($imagen);
    $alto = imagesy($imagen);

    // Calcular nuevo tamaño (reducir a la mitad)
    $nuevo_ancho = $ancho / 2;
    $nuevo_alto = $alto / 2;

    // Crear nueva imagen
    $nueva_imagen = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);

    // Redimensionar
    imagecopyresampled($nueva_imagen, $imagen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);

    // Capturar la imagen en un buffer
    ob_start();
    imagejpeg($nueva_imagen, null, $calidad);
    $imagen_comprimida = ob_get_clean();

    // Liberar memoria
    imagedestroy($imagen);
    imagedestroy($nueva_imagen);

    return $imagen_comprimida;
}
?>