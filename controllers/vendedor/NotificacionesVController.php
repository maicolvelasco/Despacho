<?php
// controllers/notificaciones/NotificacionesVController.php

session_start();
require_once '../../config/config.php'; // Asegúrate de que esta ruta es correcta
require_once '../../models/ModeloNotificacionV.php';

// Verificar autenticación y rol de usuario
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Vendedor') {
    // Redirigir al login si no está autenticado o no tiene el rol correcto
    header("Location: ../../views/login.php");
    exit();
}

// Obtener el ID del usuario logueado
$usuario_id = $_SESSION['usuario_id'];

// Crear instancia del modelo de notificaciones para Vendedor
$notificacionVModel = new ModeloNotificacionV($pdo);

// Manejar solicitudes POST para actualizar 'modificado' o 'estado_caducado_vendedor' de una notificación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar la solicitud AJAX
    // Establecer cabecera de respuesta
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';

    if ($action === 'marcar_modificado_v' && !empty($id)) {
        // Validar que el ID sea un número entero
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
            exit();
        }

        try {
            $rowCount = $notificacionVModel->actualizarModificadoV($id);
            if ($rowCount > 0) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado. Verifica el ID.']);
            }
        } catch (Exception $e) {
            // Log del error
            error_log("Error al actualizar 'modificado' para recibir: " . $e->getMessage());
            // Devuelve el mensaje de error para depuración (solo en desarrollo)
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el estado: ' . $e->getMessage()]);
        }
        exit();
    } elseif ($action === 'marcar_caducado_v' && !empty($id)) {
        // Validar que el ID sea un número entero
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
            exit();
        }

        try {
            $rowCount = $notificacionVModel->actualizarEstadoCaducadoV($id);
            if ($rowCount > 0) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado de caducado. Verifica el ID.']);
            }
        } catch (Exception $e) {
            // Log del error
            error_log("Error al actualizar 'estado_caducado_vendedor' para envios: " . $e->getMessage());
            // Devuelve el mensaje de error para depuración (solo en desarrollo)
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el estado de caducado: ' . $e->getMessage()]);
        }
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Acción inválida.']);
        exit();
    }
}

// Si es GET, obtener las notificaciones filtradas por usuario
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $notificacionesNoModificadoV = $notificacionVModel->obtenerNotificacionesNoModificadoV($usuario_id);
        $enviosCaducadosNoModificadoV = $notificacionVModel->obtenerEnviosCaducadosNoModificadoV($usuario_id);
        $notificacionesModificadoV = $notificacionVModel->obtenerNotificacionesModificadoV($usuario_id);
        $enviosCaducadosV = $notificacionVModel->obtenerEnviosCaducadosV($usuario_id);
    } catch (Exception $e) {
        // Log del error
        error_log("Error al obtener notificaciones de recibir o envios: " . $e->getMessage());
        // Manejar el error según sea necesario, por ejemplo, redirigir o mostrar un mensaje de error
        $notificacionesNoModificadoV = [];
        $enviosCaducadosNoModificadoV = [];
        $notificacionesModificadoV = [];
        $enviosCaducadosV = [];
    }
}

// Contar las notificaciones no modificadas y envíos caducados no modificados para mostrar un contador en la vista
try {
    $countNoModificadoV = $notificacionVModel->contarNotificacionesNoModificadoV($usuario_id);
    $countEnviosCaducadosNoModificadoV = $notificacionVModel->contarEnviosCaducadosNoModificadoV($usuario_id);
} catch (Exception $e) {
    error_log("Error al contar notificaciones no modificadas o envíos caducados: " . $e->getMessage());
    $countNoModificadoV = 0;
    $countEnviosCaducadosNoModificadoV = 0;
}
?>