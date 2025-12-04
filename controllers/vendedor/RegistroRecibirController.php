<?php
// controllers/vendedor/RegistroRecibirController.php

session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Vendedor') {
    header("Location: ../../views/login.php");
    exit();
}

require_once '../../config/config.php';
require_once '../../models/ModeloRegistroV.php';

// Inicializar variables para mensajes
$success = '';
$error = '';

// Obtener transportes
$transportes = getTransportes($pdo);

// Obtener remisiones disponibles para el usuario
$remisiones = getRemisionesDisponibles($pdo, $_SESSION['usuario_id']);

// Variables para almacenar datos de remisión seleccionada
$cliente_id = null;
$cliente_nombre = null;
$vendedor_id = null;
$vendedor_nombre = null;
$transporte_id = null;
$titular_id = null;
$titular_nombre = null;
$palletsRemision = [];
$departamentoNombre = ''; // Inicializar variable para el departamento

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar el formulario
    $remision_id = isset($_POST['remision_id']) ? intval($_POST['remision_id']) : 0;
    $conductor = isset($_POST['conductor']) ? trim($_POST['conductor']) : '';
    $placa = isset($_POST['placa']) ? trim($_POST['placa']) : '';
    $transporte_id = isset($_POST['transporte_id']) ? intval($_POST['transporte_id']) : 0;
    $pallets = isset($_POST['pallets']) ? $_POST['pallets'] : [];
    $imagenes = isset($_POST['captured_images']) ? $_POST['captured_images'] : [];
    $descripcion_imagen = isset($_POST['descripcion_imagen']) ? $_POST['descripcion_imagen'] : [];
    $tipo = isset($_POST['tipo']) && $_POST['tipo'] === 'propio' ? 'propio' : 'duratranz';

    // Validar campos obligatorios
    if (empty($remision_id) || empty($conductor) || empty($placa) || empty($transporte_id)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } else {
        // Validar el tipo
        if (!in_array($tipo, ['propio', 'duratranz'])) {
            $error = "Tipo de transporte inválido.";
        } else {
            // Obtener datos de cliente y vendedor asociados a la remisión
            $clienteYVendedor = getClienteYVendedorPorRemision($pdo, $remision_id);
            if (!$clienteYVendedor) {
                $error = "No se encontraron datos asociados a la remisión seleccionada.";
            } else {
                $cliente_id = $clienteYVendedor['cliente_id']; // Puede ser NULL
                $cliente_nombre = $clienteYVendedor['cliente_nombre'];
                $vendedor_id = $clienteYVendedor['vendedor_id'];
                $vendedor_nombre = $clienteYVendedor['vendedor_nombre'];
                $transporte_id = $clienteYVendedor['transporte_id'];
                $titular_id = $clienteYVendedor['titular_id'];
                $titular_nombre = $clienteYVendedor['titular_nombre'];

                // Si no hay cliente, usar el titular como cliente
                if (empty($cliente_id)) {
                    $cliente_nombre = $titular_nombre;
                }

                // Obtener el último registro de pallets para la remisión
                $palletsRemision = getUltimoRegistroRemision($pdo, $remision_id);

                // Obtener el nombre del departamento
                if (!empty($palletsRemision)) {
                    $departamentoNombre = $palletsRemision[0]['departamento_nombre'];
                }

                $palletsCantidadActual = [];
                foreach ($palletsRemision as $palletData) {
                    $palletsCantidadActual[$palletData['pallet_id']] = $palletData['cantidad'];
                }

                // Validar que las cantidades ingresadas no sobrepasen las cantidades actuales
                foreach ($pallets as $pallet) {
                    $pallet_id = isset($pallet['pallet_id']) ? intval($pallet['pallet_id']) : 0;
                    $cantidadIngresada = isset($pallet['cantidad']) ? intval($pallet['cantidad']) : 0;

                    if ($pallet_id === 0 || $cantidadIngresada <= 0) {
                        $error = "Datos de pallet inválidos.";
                        break;
                    }

                    $cantidadActual = isset($palletsCantidadActual[$pallet_id]) ? $palletsCantidadActual[$pallet_id] : 0;

                    if ($cantidadIngresada > $cantidadActual) {
                        $error = "La cantidad ingresada para el pallet ID {$pallet_id} excede la cantidad actual ({$cantidadActual}).";
                        break;
                    }
                }

                if (empty($error)) {
                    try {
                        // Iniciar transacción
                        $pdo->beginTransaction();

                        // Insertar la recepción incluyendo el tipo
                        insertarRecibir(
                            $pdo, 
                            $remision_id, 
                            $conductor, 
                            $placa, 
                            $cliente_id, // Puede ser NULL
                            $_SESSION['usuario_id'], 
                            $transporte_id, 
                            $titular_id, // Puede ser NULL
                            $imagenes, 
                            $descripcion_imagen, 
                            $pallets,
                            $tipo // Nuevo parámetro
                        );

                        // Confirmar transacción
                        $pdo->commit();

                        // Establecer mensaje de éxito en la sesión
                        $_SESSION['success'] = "Recepción registrada exitosamente.";

                        // Redirigir al dashboard
                        header("Location: Vendedor_Dashboard.php");
                        exit();
                    } catch (Exception $e) {
                        // Revertir transacción en caso de error
                        $pdo->rollBack();
                        $error = "Error al registrar la recepción: " . $e->getMessage();
                    }
                }
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remision_id'])) {
    // Obtener datos de la remisión seleccionada
    $remision_id = intval($_GET['remision_id']);
    $clienteYVendedor = getClienteYVendedorPorRemision($pdo, $remision_id);
    if ($clienteYVendedor) {
        $cliente_id = $clienteYVendedor['cliente_id']; // Puede ser NULL
        $cliente_nombre = $clienteYVendedor['cliente_nombre'];
        $vendedor_id = $clienteYVendedor['vendedor_id'];
        $vendedor_nombre = $clienteYVendedor['vendedor_nombre'];
        $transporte_id = $clienteYVendedor['transporte_id'];
        $titular_id = $clienteYVendedor['titular_id'];
        $titular_nombre = $clienteYVendedor['titular_nombre'];

        // Si no hay cliente, usar el titular como cliente
        if (empty($cliente_id)) {
            $cliente_nombre = $titular_nombre;
        }

        $palletsRemision = getUltimoRegistroRemision($pdo, $remision_id);

        // Obtener el nombre del departamento
        if (!empty($palletsRemision)) {
            $departamentoNombre = $palletsRemision[0]['departamento_nombre'];
        }

        // Si todos los pallets tienen cantidad cero, no mostrar la remisión
        if (empty($palletsRemision)) {
            $error = "La remisión seleccionada no tiene pallets disponibles.";
            $cliente_id = null;
            $cliente_nombre = null;
            $vendedor_id = null;
            $vendedor_nombre = null;
            $transporte_id = null;
            $titular_id = null;
            $titular_nombre = null;
        }
    } else {
        $error = "Datos de remisión inválidos.";
    }
}

// Cerrar la conexión
$pdo = null;
?>