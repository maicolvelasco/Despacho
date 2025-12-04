<?php
// controllers/despacho/DespachoController.php

session_start();
require_once '../../config/config.php';
require_once '../../models/ModeloDespacho.php';

// Verificar autenticación y rol de usuario
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Despacho') {
    header("Location: ../../views/login.php");
    exit();
}

// Crear instancia del modelo de despacho
$despachoModel = new ModeloDespacho($pdo);

// Manejar solicitudes POST para aceptar o eliminar una recepción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';

    if ($action === 'aceptar' && !empty($id)) {
        try {
            // Verificar si el id es válido
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'El ID es vacío o inválido.']);
                exit();
            }

            // Usar el método aceptarRecepcion del modelo
            $exito = $despachoModel->aceptarRecepcion($id);

            if ($exito) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo aceptar la recepción.']);
            }
            exit();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo aceptar la recepción. Error: ' . $e->getMessage()]);
        }
        exit();
    }
}

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
// Obtener la cantidad de 'Todos' (modificado = 'no' y observacion no null)
try {
    // Contamos tanto las notificaciones "Recientes" (no modificadas) como las "Caducadas"
    $countTodos = $notificacionModel->contarNotificacionesNoModificado() + $notificacionModel->contarNotificacionesCaducadas(); // Nuevo conteo
} catch (Exception $e) {
    error_log("Error al contar 'Todos' en DespachoController.php: " . $e->getMessage());
    $countTodos = 0;
}
// Obtener datos para la vista del dashboard
$notificacionesNoModificado = $notificacionModel->obtenerNotificacionesNoModificado();
$notificacionesModificado = $notificacionModel->obtenerNotificacionesModificado();
$envios = $despachoModel->obtenerEnvios();
$recibirEnTransito = $despachoModel->obtenerRecibirEnTransito();
$recibirRecibidos = $despachoModel->obtenerRecibirRecibidos();
?>