<?php
// controllers/despacho/DetallesController.php

session_start();
require_once '../../config/config.php';
require_once '../../models/ModeloDetalles.php';
require_once '../../models/ModeloDespacho.php';

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Despacho') {
    header("Location: ../../views/login.php");
    exit();
}

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

if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    echo "Datos inválidos.";
    exit();
}

$id = intval($_GET['id']);
$tipo = $_GET['tipo'];

// Validar tipo
if ($tipo !== 'envio' && $tipo !== 'recibir') {
    echo "Tipo inválido.";
    exit();
}

// Crear una instancia del modelo
$modeloDetalles = new ModeloDetalles($pdo);

// Obtener los detalles según el tipo y el id
$detalle = $modeloDetalles->obtenerDetalles($id, $tipo);

if (!$detalle) {
    echo "Registro no encontrado.";
    exit();
}

// Obtener los pallets asociados
$pallets = $modeloDetalles->obtenerPallets($id, $tipo);

// Obtener las imágenes asociadas
$imagenes = $modeloDetalles->obtenerImagenes($detalle, $tipo);

// Obtener el nombre del usuario
$usuario_nombre = $detalle['usuario_nombre'] ? $detalle['usuario_nombre'] : 'No asignado';

// Obtener el tipo de envío o recepción
if ($tipo === 'envio') {
    $tipo_envio = ucfirst($detalle['tipo_envio']); // 'Propio' o 'Duratranz'
} else {
    $tipo_recibir = ucfirst($detalle['tipo_recibir']); // 'Propio' o 'Duratranz'
}

// Cerrar la conexión
$pdo = null;
?>