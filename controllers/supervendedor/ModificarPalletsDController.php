<?php
// /controllers/vendedor/ModificarPalletsController.php
session_start();

// Mostrar errores para depuración (puedes desactivarlos en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../views/login.php");
    exit();
}

require_once '../../config/config.php';
require_once '../../models/ModeloDetallesD.php';

// Obtener información del usuario actual
$user_id = $_SESSION['usuario_id'];
$stmt_user = $pdo->prepare("SELECT rol_id, departamento_id FROM usuarios WHERE id = :id");
$stmt_user->execute(['id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "Usuario no encontrado.";
    header("Location: ../../views/login.php");
    exit();
}

$rol_id = intval($user['rol_id']);
$departamento_id = intval($user['departamento_id']);

// Validar que el usuario tenga rol 2 (Vendedor) o 4 (Supervendedor)
if ($rol_id !== 2 && $rol_id !== 4) {
    $_SESSION['error'] = "No tienes permisos para acceder a esta página.";
    header("Location: ../../views/login.php");
    exit();
}

// Inicializar el modelo
$modeloDetalleD = new ModeloDetalleD($pdo);

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
    $detalle = $modeloDetalleD->getDetalleById($id, $tipo);

    if (!$detalle) {
        echo "No se encontró el registro.";
        exit();
    }

    // Obtener los pallets asociados
    $pallets = $modeloDetalleD->getPalletsById($id, $tipo);

    // Obtener la observación existente
    $observacion = $detalle['observacion'] ?? '';

    // Obtener las imágenes asociadas
    $imagenes = $modeloDetalleD->getImagenesByDetalle($id, $tipo);

    // Extraer el nombre del vendedor
    $usuario_nombre = $detalle['usuario_nombre'] ?? '';

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manejar la solicitud POST para procesar la modificación
    if (!isset($_POST['id']) || !isset($_POST['tipo']) || !isset($_POST['pallets'])) {
        $_SESSION['error'] = "Datos incompletos.";
        header("Location: Modificar_PalletsD.php?id={$_POST['id']}&tipo={$_POST['tipo']}");
        exit();
    }

    $id = intval($_POST['id']);
    $tipo = $_POST['tipo'];
    $pallets = $_POST['pallets']; // Array de pallets
    $observacion = trim($_POST['observacion']);

    // Validar el tipo
    if ($tipo !== 'envio' && $tipo !== 'recibir') {
        $_SESSION['error'] = "Tipo inválido.";
        header("Location: Modificar_PalletsD.php?id={$id}&tipo={$tipo}");
        exit();
    }

    // Validar la longitud de la observación
    if (strlen($observacion) > 1000) {
        $_SESSION['error'] = "La observación no puede exceder los 1000 caracteres.";
        header("Location: Modificar_PalletsD.php?id={$id}&tipo={$tipo}");
        exit();
    }

    // Validar las cantidades de pallets (asegurarse de que sean números no negativos)
    foreach ($pallets as $pallet) {
        if (!isset($pallet['cantidad']) || !is_numeric($pallet['cantidad']) || intval($pallet['cantidad']) < 0) {
            $_SESSION['error'] = "Cantidad de pallets inválida.";
            header("Location: Modificar_PalletsD.php?id={$id}&tipo={$tipo}");
            exit();
        }
    }

    // Actualizar los pallets y ajustar el stock utilizando el método actualizado
    $resultado = $modeloDetalleD->actualizarPalletsYEstado($id, $tipo, $pallets, $observacion);

    if ($resultado) {
        // Almacenar mensaje de éxito en la sesión
        $_SESSION['mensaje'] = "¡Pallets y observación actualizados correctamente!";
        header("Location: ../../views/supervendedor/DetallesD.php?id={$id}&tipo={$tipo}");
        exit();
    } else {
        // Almacenar mensaje de error en la sesión
        $_SESSION['error'] = "Error al actualizar los datos.";
        header("Location: Modificar_PalletsD.php?id={$id}&tipo={$tipo}");
        exit();
    }
} else {
    echo "Método no soportado.";
    exit();
}
?>