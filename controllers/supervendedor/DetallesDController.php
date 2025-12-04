<?php
// /controllers/vendedor/DetalleVentaController.php
session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Supervendedor') {
    header("Location: ../../views/login.php");
    exit();
}

require_once '../../config/config.php';
require_once '../../models/ModeloDetallesD.php';
require_once '../../models/ModeloSupervendedor.php';

// Validar la existencia de los parámetros GET
if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    echo "Datos inválidos.";
    exit();
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

$id = intval($_GET['id']);
$tipo = $_GET['tipo'];

// Validar el tipo
if ($tipo !== 'envio' && $tipo !== 'recibir') {
    echo "Tipo inválido.";
    exit();
}

// Inicializar el modelo
$modeloDetalleD = new ModeloDetalleD($pdo);

// Obtener los detalles según el tipo y el id
$detalle = $modeloDetalleD->getDetalleById($id, $tipo);

if (!$detalle) {
    echo "Registro no encontrado.";
    exit();
}

// Obtener los pallets asociados
$pallets = $modeloDetalleD->getPalletsById($id, $tipo);

// Obtener las imágenes asociadas
if ($tipo === 'envio') {
    $detalle_id = $detalle['envio_id'];
} else { // recibir
    $detalle_id = $detalle['recibir_id'];
}

$imagenes = $modeloDetalleD->getImagenesByDetalle($detalle_id, $tipo);

// Obtener el nombre del usuario
$usuario_nombre = !empty($detalle['usuario_nombre']) ? $detalle['usuario_nombre'] : 'No asignado';

// Obtener el tipo de envío o recepción
if ($tipo === 'envio') {
    $tipo_envio = ucfirst($detalle['tipo_envio']); // 'Propio' o 'Duratranz'
} else {
    $tipo_recibir = ucfirst($detalle['tipo_recibir']); // 'Propio' o 'Duratranz'
}

// Cerrar la conexión
$pdo = null;
?>