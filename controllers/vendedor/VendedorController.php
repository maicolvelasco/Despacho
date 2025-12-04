<?php
// /controllers/vendedor/VendedorController.php
session_start();

require_once '../../config/config.php';
require_once '../../models/ModeloVendedor.php';
require_once '../../models/ModeloNotificacionV.php'; // Añadido

// Verificar autenticación y rol de usuario
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Vendedor') {
    header("Location: ../../views/login.php");
    exit();
}

// Crear una instancia del modelo de vendedor
$vendedorModel = new ModeloVendedor($pdo);

// Crear instancia del modelo de notificaciones
$notificacionModel = new ModeloNotificacionV($pdo);

// Obtener el ID del usuario logueado
$usuario_id = intval($_SESSION['usuario_id']);

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
// Manejar solicitudes POST para aceptar o eliminar envíos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Establecer el encabezado para la respuesta JSON
    header('Content-Type: application/json');

    // Obtener y sanitizar las entradas
    $action = isset($_POST['action']) ? filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) : '';
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit();
    }

    if ($action === 'aceptar_envio') {
        try {
            // Actualizar el estado del envío a 'completado'
            $resultado = $vendedorModel->aceptarEnvio($id, $usuario_id);
            if ($resultado > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Envío aceptado exitosamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Envío no encontrado o ya está completado.']);
            }
        } catch (PDOException $e) {
            // Manejar errores de la base de datos
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el envío: ' . $e->getMessage()]);
        }
        exit();
    }

    if ($action === 'eliminar_envio') {
        try {
            // Eliminar el envío
            $resultado = $vendedorModel->eliminarEnvio($id, $usuario_id);
            if ($resultado > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Envío eliminado exitosamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Envío no encontrado o ya está eliminado.']);
            }
        } catch (PDOException $e) {
            // Manejar errores de la base de datos
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el envío: ' . $e->getMessage()]);
        }
        exit();
    }

    // Si la acción no es reconocida
    echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
    exit();
}

// Contar las notificaciones no modificadas para mostrar un contador en la vista
try {
    $countTodos = $notificacionModel->contarNotificacionesNoModificadoV($usuario_id) + $notificacionModel->contarEnviosCaducadosNoModificadoV($usuario_id); // Pasar $usuario_id
} catch (Exception $e) {
    error_log("Error al contar 'Todos' en VendedorController.php: " . $e->getMessage());
    $countTodos = 0;
}

// Obtener las notificaciones filtradas por usuario
try {
    $notificacionesNoModificadoV = $notificacionModel->obtenerNotificacionesNoModificadoV($usuario_id); // Pasar $usuario_id
    $notificacionesModificadoV = $notificacionModel->obtenerNotificacionesModificadoV($usuario_id); // Pasar $usuario_id
} catch (Exception $e) {
    error_log("Error al obtener notificaciones de recibir: " . $e->getMessage());
    $notificacionesNoModificadoV = [];
    $notificacionesModificadoV = [];
}

// Si la solicitud no es POST, cargar el dashboard como antes
$recibirRecibidos = $vendedorModel->obtenerRecibirRecibidos($usuario_id);
$enviosEnTransito = $vendedorModel->obtenerEnviosEnTransito($usuario_id);
$enviosCompletados = $vendedorModel->obtenerEnviosCompletados($usuario_id);
?>