<?php
// controllers/despacho/ModificarRecibirController.php
session_start();

// Mostrar errores para depuración (puedes desactivarlos en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../../views/login.php");
    exit();
}

require_once '../../config/config.php';
require_once '../../models/ModeloDetallesA.php';

// Inicializar el modelo
$modeloDetalles = new ModeloDetalles($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Manejar la solicitud GET para mostrar el formulario de modificación
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

    // Obtener los detalles generales
    $detalle = $modeloDetalles->obtenerDetalles($id, $tipo);

    if (!$detalle) {
        echo "No se encontró el registro.";
        exit();
    }

    // Obtener los pallets asociados
    $pallets = $modeloDetalles->obtenerPallets($id, $tipo);

    // Obtener la observación existente
    $observacion = $detalle['observacion'] ?? '';

    // Obtener las imágenes asociadas
    $imagenes = $modeloDetalles->obtenerImagenes($detalle, $tipo);

    // Extraer el nombre del vendedor
    $usuario_nombre = $detalle['usuario_nombre'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manejar la solicitud POST para procesar la modificación
    if (!isset($_POST['id']) || !isset($_POST['tipo']) || !isset($_POST['pallets'])) {
        $_SESSION['error'] = "Datos incompletos.";
        header("Location: Modificar_EnvioA.php?id={$_POST['id']}&tipo={$_POST['tipo']}");
        exit();
    }

    $id = intval($_POST['id']);
    $tipo = $_POST['tipo'];
    $pallets = $_POST['pallets']; // Array de pallets
    $observacion = trim($_POST['observacion']);

    // Validar el tipo
    if ($tipo !== 'envio' && $tipo !== 'recibir') {
        $_SESSION['error'] = "Tipo inválido.";
        header("Location: Modificar_EnvioA.php?id={$id}&tipo={$tipo}");
        exit();
    }

    // Validar la longitud de la observación
    if (strlen($observacion) > 1000) {
        $_SESSION['error'] = "La observación no puede exceder los 1000 caracteres.";
        header("Location: Modificar_EnvioA.php?id={$id}&tipo={$tipo}");
        exit();
    }

    // Validar las cantidades de pallets (asegurarse de que sean números no negativos)
    foreach ($pallets as $pallet) {
        if (!isset($pallet['cantidad']) || !is_numeric($pallet['cantidad']) || intval($pallet['cantidad']) < 0) {
            $_SESSION['error'] = "Cantidad de pallets inválida.";
            header("Location: Modificar_RecibirRA.php?id={$id}&tipo={$tipo}");
            exit();
        }
    }

    // Actualizar los pallets y ajustar el stock utilizando el método actualizado
    $resultado = $modeloDetalles->actualizarPalletsYEstado($id, $tipo, $pallets, $observacion);

    if ($resultado) {
        // Almacenar mensaje de éxito en la sesión
        $_SESSION['mensaje'] = "¡Pallets y observación actualizados correctamente!";
        header("Location: ../../views/admin/DetallesA.php?id={$id}&tipo={$tipo}");
        exit();
    } else {
        // Almacenar mensaje de error en la sesión
        $_SESSION['error'] = "Error al actualizar los datos.";
        header("Location: Modificar_RecibirRA.php?id={$id}&tipo={$tipo}");
        exit();
    }
} else {
    echo "Método no soportado.";
    exit();
}
?>