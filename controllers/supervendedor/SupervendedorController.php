<?php
// controllers/supervendedor/SupervendedorController.php

session_start();

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config/config.php';
require_once '../../models/ModeloSupervendedor.php';
require_once '../../models/ModeloNotificacion.php';

// Verificar autenticación y rol de usuario
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Supervendedor') {
    header("Location: ../../views/login.php");
    exit();
}

// Crear instancia del modelo de supervendedor
$supervendedorModel = new ModeloSupervendedor($pdo);

// Crear instancia del modelo de notificaciones
$notificacionModel = new ModeloNotificacion($pdo);

function obtenerColorDepartamento($nombreDepartamento) {
    $coloresDepartamentos = [
        'Cochabamba' => 'bg-info',     // Rojo
        'Santa Cruz' => 'bg-success',    // Azul
        'La Paz' => 'bg-danger',        // Verde
        'Oruro' => 'bg-warning',         // Amarillo
        'Potosí' => 'bg-light text-dark',           // Celeste
        'Sucre' => 'bg-dark',            // Negro
        'Tarija' => 'bg-secondary',      // Gris
        'Pando' => 'bg-primary', // Blanco con texto oscuro
        'Beni' => 'bg-transparent text-dark'            // Púrpura (necesitarás agregar este color personalizado)
    ];

    return $coloresDepartamentos[$nombreDepartamento] ?? 'bg-secondary'; // Color por defecto si no se encuentra
}
// Manejar solicitudes POST para aceptar o eliminar una recepción o un envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if ($action === 'aceptar' && !empty($id) && !empty($tipo)) {
        if ($tipo === 'recibir') {
            // Actualizar el estado de la recepción a 'recibido'
            $affectedRows = $supervendedorModel->aceptarRecepcion($id);
        } elseif ($tipo === 'envio') {
            // Actualizar el estado del envío a 'entregado' (o el estado que corresponda)
            $affectedRows = $supervendedorModel->aceptarEnvio($id);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tipo inválido.']);
            exit();
        }

        if ($affectedRows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado.']);
        }
        exit();
    } elseif ($action === 'eliminar' && !empty($id)) {
        // Eliminar la recepción
        $affectedRows = $supervendedorModel->eliminarRecepcion($id);
        if ($affectedRows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la recepción.']);
        }
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Acción inválida.']);
        exit();
    }
}

// Obtener la cantidad de 'Todos' (modificado = 'no' y observacion no null)
try {
    // Contamos tanto las notificaciones "Recientes" (no modificadas) como las "Caducadas"
    $countTodos = $notificacionModel->contarNotificacionesNoModificado() + $notificacionModel->contarNotificacionesCaducadas(); // Nuevo conteo
} catch (Exception $e) {
    error_log("Error al contar 'Todos' en SupervendedorController.php: " . $e->getMessage());
    $countTodos = 0;
}

// Obtener datos para la vista del dashboard
$notificacionesNoModificado = $notificacionModel->obtenerNotificacionesNoModificado();
$notificacionesModificado = $notificacionModel->obtenerNotificacionesModificado();

// Obtener el departamento del usuario logueado
$user_id = $_SESSION['usuario_id'];
$department_id = $supervendedorModel->getDepartmentIdByUserId($user_id);

if ($department_id === null) {
    // Manejar el error, el usuario no tiene departamento
    $totalPallets = 0;
    $contador = 0;
    $pallets = [];
    $envios = [];
    $recibirEnTransito = [];
    $recibirRecibidos = [];
    $enviosEnTransito = [];
    $enTransito = [];
    $enTransitoCount = 0;
} else {
    $totalPallets = $supervendedorModel->getTotalPalletsByDepartment($department_id);
    $contador = $supervendedorModel->getCaducadosContadorByDepartment($department_id);
    $pallets = $supervendedorModel->getPalletsByDepartment($department_id);

    // **Filtrar los envíos y recepciones por departamento**
    $envios = $supervendedorModel->obtenerEnviosPorDepartamento($department_id);
    $recibirEnTransito = $supervendedorModel->obtenerRecibirEnTransitoPorDepartamento($department_id);
    $recibirRecibidos = $supervendedorModel->obtenerRecibirRecibidosPorDepartamento($department_id);
    $enviosEnTransito = $supervendedorModel->obtenerEnviosEnTransitoPorDepartamento($department_id);

    // Combinar los en tránsito de recibir y envío
    $enTransito = [];

    foreach ($recibirEnTransito as $item) {
        $item['tipo_en_transito'] = 'recibir';
        $item['id'] = $item['recibir_id'];
        $item['fecha_inicio'] = $item['fecha'];
        $item['fecha_fin'] = ''; // No aplica para recibir
        $enTransito[] = $item;
    }

    foreach ($enviosEnTransito as $item) {
        $item['tipo_en_transito'] = 'envio';
        $item['id'] = $item['envio_id'];
        // 'fecha_inicio' y 'fecha_fin' ya existen
        $enTransito[] = $item;
    }

    // Contador de en tránsito
    $enTransitoCount = count($enTransito);
}
?>