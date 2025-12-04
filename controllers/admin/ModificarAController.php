<?php
// controllers/despacho/ModificarController.php
session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../../views/login.php");
    exit();
}

require_once '../../config/config.php';
require_once '../../models/ModeloModificarA.php';

// Inicializar el modelo
$modeloModificar = new ModeloModificar($pdo);

// Inicializar variables para mensajes
$success = '';
$error = '';

// Obtener el ID del envío a modificar
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $envio_id = (int)$_GET['id'];
} else {
    // Redirigir si no hay ID válido
    header("Location: Control.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos del formulario
    $data = [
        'envio_id' => $envio_id,
        'tipo_envio' => $_POST['tipo_envio'], // 'propio' o 'duratranz'
        'pallets' => $_POST['pallets'] ?? [],
        'captured_images' => $_POST['captured_images'] ?? [],
        'descripcion_imagen' => $_POST['descripcion_imagen'] ?? [],
    ];

    // Validar campos obligatorios
    $requiredFields = ['tipo_envio'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $error = "Por favor, completa todos los campos obligatorios.";
            break;
        }
    }

    if (empty($error)) {
        try {
            // Delegar la actualización al modelo
            $modeloModificar->actualizarEnvio($data);
            $_SESSION['success'] = "Envío actualizado exitosamente.";
            header("Location: ../../views/admin/Control.php");
            exit();
        } catch (Exception $e) {
            $error = "Error al editar el envío: " . $e->getMessage();
        }
    }
}

// Obtener datos para los selects (aunque no serán editables, pueden usarse para mostrar información)
$clientes = $modeloModificar->getClientes();
$titulares = $modeloModificar->getTitulares();
$transportes = $modeloModificar->getTransportes();
$usuarios = $modeloModificar->getUsuariosVendedor();
$palletsDisponibles = $modeloModificar->getPallets();

// Obtener detalles del envío para prellenar el formulario
$envio = $modeloModificar->getEnvioById($envio_id);
if (!$envio) {
    // Redirigir si el envío no existe
    header("Location: Control.php");
    exit();
}

// Obtener pallets asociados e imágenes
$pallets_asociados = $modeloModificar->getPalletsAsociados($envio_id);
$imagenes = $modeloModificar->getImagenesEnvio($envio_id);

// Cerrar la conexión
$pdo = null;
?>