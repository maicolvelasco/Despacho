<?php
// /controllers/vendedor/DetalleVentaController.php
session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Vendedor') {
    header("Location: ../../views/login.php");
    exit();
}

require_once '../../config/config.php';
require_once '../../models/ModeloDetalleV.php';
require_once '../../models/ModeloVendedor.php';

// Manejar solicitudes POST para aceptar envíos
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
            // Crear instancia del modelo de vendedor
            $vendedorModel = new ModeloVendedor($pdo);
            
            // Obtener el ID del usuario logueado
            $usuario_id = intval($_SESSION['usuario_id']);

            // Actualizar el estado del envío a 'completado'
            $resultado = $vendedorModel->aceptarEnvio($id, $usuario_id);
            
            if ($resultado) {
                echo json_encode(['status' => 'success', 'message' => 'Envío aceptado exitosamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Envío no encontrado o ya está completado.']);
            }
        } catch (PDOException $e) {
            // Manejar errores de la base de datos
            echo json_encode([
                'status' => 'error', 
                'message' => 'Error al actualizar el envío: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    // Si la acción no es reconocida
    echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
    exit();
}

// Validar la existencia de los parámetros GET
if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    echo "Datos inválidos.";
    exit();
}

$id = intval($_GET['id']);
$tipo = $_GET['tipo'];

// Validar el tipo
if ($tipo !== 'envio' && $tipo !== 'recibir') {
    echo "Tipo inválido.";
    exit();
}

// Inicializar el modelo
$modeloDetalleV = new ModeloDetalleV($pdo);

// Obtener los detalles según el tipo y el id
$detalle = $modeloDetalleV->getDetalleById($id, $tipo);

if (!$detalle) {
    echo "Registro no encontrado.";
    exit();
}

// Obtener los pallets asociados
$pallets = $modeloDetalleV->getPalletsById($id, $tipo);

// Obtener las imágenes asociadas
if ($tipo === 'envio') {
    $detalle_id = $detalle['envio_id'];
} else { // recibir
    $detalle_id = $detalle['recibir_id'];
}

$imagenes = $modeloDetalleV->getImagenesByDetalle($detalle_id, $tipo);

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