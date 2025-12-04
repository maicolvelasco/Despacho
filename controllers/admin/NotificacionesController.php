<?php
// controllers/notificaciones/NotificacionesController.php

session_start();
require_once '../../config/config.php';
require_once '../../models/ModeloNotificacion.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['Administrador'])) {
    header("Location: ../../views/login.php");
    exit();
}

$notificacionModel = new ModeloNotificacion($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';

    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
        exit();
    }

    error_log("Recibido action: $action con ID: $id"); // Log para confirmar el ID y la acción

    try {
        if ($action === 'marcar_modificado') {
            $rowCount = $notificacionModel->actualizarModificado($id);
            echo json_encode(['status' => $rowCount > 0 ? 'success' : 'error', 'message' => $rowCount > 0 ? 'Actualizado correctamente' : 'No se pudo actualizar el estado modificado']);
        } elseif ($action === 'marcar_caducado') {
            $rowCount = $notificacionModel->actualizarEstadoCaducidad($id);
            echo json_encode(['status' => $rowCount > 0 ? 'success' : 'error', 'message' => $rowCount > 0 ? 'Estado de caducidad actualizado' : 'No se pudo actualizar el estado de caducidad']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        }
    } catch (Exception $e) {
        error_log("Error en NotificacionesController: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al realizar la actualización']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $notificacionesNoModificado = $notificacionModel->obtenerNotificacionesNoModificado();
        $notificacionesModificado = $notificacionModel->obtenerNotificacionesModificado();
        $notificacionesCaducadas = $notificacionModel->obtenerNotificacionesCaducadas();
    } catch (Exception $e) {
        error_log("Error al obtener notificaciones: " . $e->getMessage());
        $notificacionesNoModificado = [];
        $notificacionesModificado = [];
        $notificacionesCaducadas = [];
    }
}
?>