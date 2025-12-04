<?php
// controllers/despacho/RegistroController.php

session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Supervendedor') {
    header("Location: ../../views/login.php");
    exit();
}

require_once '../../config/config.php';
require_once '../../models/ModeloRegistroD.php';

// Inicializar variables para mensajes
$success = '';
$error = '';

// Obtener el ID del usuario logueado
$current_user_id = $_SESSION['usuario_id'];

// Obtener el departamento del usuario logueado
$current_user_department = getUserDepartamento($pdo, $current_user_id);

// Obtener datos para los selects
$clientes = getClientes($pdo);
$transportes = getTransportes($pdo);
$usuarios = getUsuariosVendedor($pdo);
$palletsDisponibles = getPallets($pdo);
$departamentosConPallets = getDepartmentsWithPallets($pdo);

// Agrupar pallets por departamento para uso en la vista
$departamento_pallets = [];
foreach ($departamentosConPallets as $departamento) {
    $departamento_pallets[$departamento['id']] = [];
}
foreach ($palletsDisponibles as $pallet) {
    if (isset($departamento_pallets[$pallet['departamento_id']])) {
        $departamento_pallets[$pallet['departamento_id']][] = $pallet;
    }
}

// Determinar la fecha de inicio por defecto
$fecha_inicio = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST['fecha_inicio'] : date('Y-m-d');

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $numero_remision = isset($_POST['numero_remision']) ? trim($_POST['numero_remision']) : '';
    $conductor = isset($_POST['conductor']) ? trim($_POST['conductor']) : '';
    $placa = isset($_POST['placa']) ? trim($_POST['placa']) : '';
    $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : ''; // Campo de código
    $transporte_id = isset($_POST['transporte_id']) ? $_POST['transporte_id'] : '';
    $tipo = isset($_POST['tipo']) && $_POST['tipo'] === 'propio' ? 'propio' : 'duratranz'; // Campo tipo
    // Eliminamos la línea que obtenía usuario_id de la sesión
    // $usuario_id = $_SESSION['usuario_id'];
    $pallets = isset($_POST['pallets']) ? $_POST['pallets'] : [];
    $imagenes = isset($_POST['captured_images']) ? $_POST['captured_images'] : [];
    $descripcion_imagen = isset($_POST['descripcion_imagen']) ? $_POST['descripcion_imagen'] : [];

    // Validar campos obligatorios
    if (empty($numero_remision) || empty($conductor) || empty($placa) || empty($codigo) || empty($transporte_id) || empty($tipo)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } else {
        // Verificar si el número de remisión ya existe
        if (verificarNumeroRemision($pdo, $numero_remision)) {
            $error = "El número de remisión ya existe. Por favor, ingresa un número único.";
        } else {
            try {
                // Determinar si el código es de un titular o cliente
                $titular = getTitularByCode($pdo, $codigo);
                $cliente = getClienteByCode($pdo, $codigo);

                if ($titular) {
                    // Código corresponde a un Titular
                    $titular_id = $titular['id'];
                    $cliente_id = null; // No se llena cliente_id
                    $cliente_nombre = $titular['nombre'];
                    $titular_nombre = $titular['nombre'];
                    $vendedor_nombre = $titular['vendedor_nombre'];
                    $vendedor_id = $titular['vendedor_id']; // Obtenemos el vendedor_id del titular
                } elseif ($cliente) {
                    // Código corresponde a un Cliente
                    $titular_id = $cliente['titular_id'];
                    $cliente_id = $cliente['id'];
                    $cliente_nombre = $cliente['nombre'];
                    $titular_nombre = $cliente['titular_nombre'];
                    $vendedor_nombre = $cliente['vendedor_nombre'];
                    $vendedor_id = $cliente['vendedor_id']; // Obtenemos el vendedor_id del cliente
                } else {
                    throw new Exception("El código ingresado no corresponde a ningún Titular o Cliente.");
                }

                // Asignar el vendedor_id como usuario_id para el envío
                $usuario_id = $vendedor_id;

                // Iniciar transacción
                $pdo->beginTransaction();

                // Insertar la nueva remisión y el envío
                $remision_id = insertarRemision($pdo, $numero_remision);
                insertarEnvio($pdo, $remision_id, $conductor, $placa, $cliente_id, $usuario_id, $transporte_id, $titular_id, $tipo, $imagenes, $descripcion_imagen, $pallets);

                // Confirmar transacción
                $pdo->commit();

                // Establecer mensaje de éxito en la sesión
                $_SESSION['success'] = "Envío registrado exitosamente. Número de Remisión: {$numero_remision}";

                // Redirigir al dashboard
                header("Location: Supervendedor_Dashboard.php");
                exit();
            } catch (Exception $e) {
                // Revertir transacción en caso de error
                $pdo->rollBack();
                $error = "Error al registrar el envío: " . $e->getMessage();
            }
        }
    }
}

// Manejar solicitudes AJAX para auto-llenar campos basados en el código
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'buscar_codigo' && isset($_GET['codigo'])) {
    $codigo = trim($_GET['codigo']);
    $response = [];

    // Buscar si el código corresponde a un Titular
    $titular = getTitularByCode($pdo, $codigo);
    if ($titular) {
        $response['tipo'] = 'titular';
        $response['titular_id'] = $titular['id'];
        $response['titular_nombre'] = $titular['nombre'];
        $response['vendedor_nombre'] = $titular['vendedor_nombre'];
        $response['cliente_nombre'] = $titular['nombre']; // El cliente es el titular
    } else {
        // Buscar si el código corresponde a un Cliente
        $cliente = getClienteByCode($pdo, $codigo);
        if ($cliente) {
            $response['tipo'] = 'cliente';
            $response['titular_id'] = $cliente['titular_id'];
            $response['cliente_id'] = $cliente['id'];
            $response['titular_nombre'] = $cliente['titular_nombre'];
            $response['cliente_nombre'] = $cliente['nombre'];
            $response['vendedor_nombre'] = $cliente['vendedor_nombre'];
        } else {
            $response['error'] = "El código ingresado no corresponde a ningún Titular o Cliente.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>