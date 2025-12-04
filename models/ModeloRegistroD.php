<?php
// models/ModeloRegistro.php

function getClientes($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, codigo, titular_id FROM clientes");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTransportes($pdo) {
    $stmt = $pdo->query("SELECT id, nombre FROM transporte");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsuariosVendedor($pdo) {
    $stmt = $pdo->prepare("SELECT u.id, u.nombre FROM usuarios u WHERE u.rol_id = 2");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPallets($pdo) {
    $stmt = $pdo->query("SELECT id, tamano, stock, departamento_id FROM pallets");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDepartmentsWithPallets($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT d.id, d.nombre FROM departamentos d JOIN pallets p ON d.id = p.departamento_id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPalletsByDepartment($pdo, $departamento_id) {
    $stmt = $pdo->prepare("SELECT id, tamano, stock FROM pallets WHERE departamento_id = :departamento_id");
    $stmt->execute(['departamento_id' => $departamento_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserDepartamento($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT d.id, d.nombre FROM usuarios u JOIN departamentos d ON u.departamento_id = d.id WHERE u.id = :usuario_id");
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function verificarNumeroRemision($pdo, $numero_remision) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM remision WHERE numero = :numero");
    $stmt->execute(['numero' => $numero_remision]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] > 0;
}

function insertarRemision($pdo, $numero_remision) {
    $stmtRemision = $pdo->prepare("INSERT INTO remision (numero) VALUES (:numero)");
    $stmtRemision->execute(['numero' => $numero_remision]);
    return $pdo->lastInsertId();
}

function insertarEnvio($pdo, $remision_id, $conductor, $placa, $cliente_id, $usuario_id, $transporte_id, $titular_id, $tipo, $imagenes, $descripcion_imagen, $pallets) {
    $fecha_inicio = date('Y-m-d');
    $fecha_fin = date('Y-m-d', strtotime($fecha_inicio . ' +30 days'));

    $stmtEnvio = $pdo->prepare("
        INSERT INTO envios (conductor, placa, fecha_inicio, fecha_fin, remision_id, cliente_id, usuario_id, transporte_id, titular_id, tipo, estado)
        VALUES (:conductor, :placa, :fecha_inicio, :fecha_fin, :remision_id, :cliente_id, :usuario_id, :transporte_id, :titular_id, :tipo, 'en_transito')
    ");
    $stmtEnvio->execute([
        'conductor' => $conductor,
        'placa' => $placa,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'remision_id' => $remision_id,
        'cliente_id' => $cliente_id, // Puede ser NULL
        'usuario_id' => $usuario_id, // Ahora es el vendedor_id
        'transporte_id' => $transporte_id,
        'titular_id' => $titular_id,
        'tipo' => $tipo
    ]);
    $envio_id = $pdo->lastInsertId();

    if (!empty($pallets) && is_array($pallets)) {
        foreach ($pallets as $pallet) {
            if (isset($pallet['pallet_id']) && isset($pallet['cantidad'])) {
                // Solo insertamos el pallet sin actualizar el stock
                $stmtPallets = $pdo->prepare("
                    INSERT INTO remision_pallets (remision_id, envio_id, pallet_id, cantidad, tipo)
                    VALUES (:remision_id, :envio_id, :pallet_id, :cantidad, 'envio')
                ");
                $stmtPallets->execute([
                    'remision_id' => $remision_id,
                    'envio_id' => $envio_id,
                    'pallet_id' => $pallet['pallet_id'],
                    'cantidad' => $pallet['cantidad']
                ]);
            }
        }
    }

    // Insertar imágenes con manejo de datos base64 y compresión
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
                INSERT INTO envios_imagenes (envio_id, imagen, descripcion)
                VALUES (:envio_id, :imagen, :descripcion)
            ");
            
            $stmtImagenes->execute([
                'envio_id' => $envio_id,
                'imagen' => $imagen_comprimida,
                'descripcion' => $descripcion
            ]);
        }
    }

    return $envio_id;
}

// Función para comprimir imagen (puedes agregarla al mismo archivo de modelo)
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

function getTitularByCode($pdo, $code) {
    $stmt = $pdo->prepare("SELECT t.id, t.nombre, t.codigo, t.usuario_id, u.nombre as vendedor_nombre, u.id as vendedor_id 
                           FROM titular t 
                           JOIN usuarios u ON t.usuario_id = u.id 
                           WHERE t.codigo = :code");
    $stmt->execute(['code' => $code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getClienteByCode($pdo, $code) {
    $stmt = $pdo->prepare("SELECT c.id, c.nombre, c.codigo, c.titular_id, t.nombre as titular_nombre, u.nombre as vendedor_nombre, u.id as vendedor_id 
                           FROM clientes c 
                           LEFT JOIN titular t ON c.titular_id = t.id 
                           JOIN usuarios u ON t.usuario_id = u.id 
                           WHERE c.codigo = :code");
    $stmt->execute(['code' => $code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>