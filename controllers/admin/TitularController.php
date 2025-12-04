<?php
// /controllers/admin/TitularController.php

require_once '../../config/config.php';
require_once '../../models/ModeloTitular.php';

session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../login.php");
    exit();
}

class TitularController
{
    public $model;

    public function __construct($pdo)
    {
        $this->model = new ModeloTitular($pdo);
    }

    /**
     * Mostrar todos los titulares.
     *
     * @return array
     */
    public function mostrarTitulares()
    {
        return $this->model->obtenerTodosTitulares();
    }    

    /**
     * Obtener todos los usuarios con rol vendedor para el dropdown.
     *
     * @return array
     */
    public function obtenerUsuariosPorRoles()
    {
        return $this->model->obtenerUsuariosPorRoles();
    }

    /**
     * Registrar un nuevo titular.
     *
     * @param string $nombre
     * @param string $codigo
     * @param int $usuario_id
     * @return array
     */
    public function registrarTitular($nombre, $codigo, $usuario_id)
    {
        return $this->model->registrarTitular($nombre, $codigo, $usuario_id);
    }

    /**
     * Obtener un titular por su ID.
     *
     * @param int $id
     * @return array|false
     */
    public function obtenerTitular($id)
    {
        return $this->model->obtenerTitularPorId($id);
    }

    /**
     * Actualizar un titular existente.
     *
     * @param int $id
     * @param string $nombre
     * @param string $codigo
     * @param int $usuario_id
     * @return array
     */
    public function actualizarTitular($id, $nombre, $codigo, $usuario_id)
    {
        return $this->model->actualizarTitular($id, $nombre, $codigo, $usuario_id);
    }

    /**
     * Eliminar un titular.
     *
     * @param int $id
     * @return array
     */
    public function eliminarTitular($id)
    {
        return $this->model->eliminarTitularPorId($id);
    }
}

// Instanciar el controlador
$controller = new TitularController($pdo);

// Manejo de la solicitud GET para obtener un titular específico
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $titularId = intval($_GET['id']);
    $titular = $controller->obtenerTitular($titularId);
    
    if ($titular) {
        echo json_encode($titular);
    } else {
        echo json_encode(['success' => false, 'error' => 'Titular no encontrado']);
    }
    exit;
}

// Manejo de la solicitud POST para registrar, actualizar, eliminar, obtener titulares por usuario y reasignar titulares
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Acción de eliminar titular
    if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de titular inválido']);
            exit;
        }

        $resultado = $controller->eliminarTitular($id);
        echo json_encode($resultado);
        exit;
    }

    // Acción de actualizar titular
    if (isset($_POST['action']) && $_POST['action'] === 'actualizar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
        $usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de titular inválido']);
            exit;
        }

        $resultado = $controller->actualizarTitular($id, $nombre, $codigo, $usuario_id);
        echo json_encode($resultado);
        exit;
    }

    // Acción de registrar titular
    if (isset($_POST['action']) && $_POST['action'] === 'registrar') {
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
        $usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;

        $resultado = $controller->registrarTitular($nombre, $codigo, $usuario_id);
        echo json_encode($resultado);
        exit;
    }

    // Acción de obtener titulares por usuario
    if (isset($_POST['action']) && $_POST['action'] === 'obtener_titulares_por_usuario') {
        $usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;

        if ($usuario_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
            exit;
        }

        $titulares = $controller->model->obtenerTitularesPorUsuario($usuario_id);
        echo json_encode(['success' => true, 'titulares' => $titulares]);
        exit;
    }

    // Acción de reasignar titulares
    if (isset($_POST['action']) && $_POST['action'] === 'reasignar_titulares') {
        $usuario_original_id = isset($_POST['usuario_original_id']) ? intval($_POST['usuario_original_id']) : 0;
        $usuario_nuevo_id = isset($_POST['usuario_nuevo_id']) ? intval($_POST['usuario_nuevo_id']) : 0;

        if ($usuario_original_id <= 0 || $usuario_nuevo_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
            exit;
        }

        // Evitar reasignar a sí mismo
        if ($usuario_original_id === $usuario_nuevo_id) {
            echo json_encode(['success' => false, 'error' => 'El usuario nuevo debe ser diferente al original']);
            exit;
        }

        $resultado = $controller->model->reasignarTitulares($usuario_original_id, $usuario_nuevo_id);
        echo json_encode($resultado);
        exit;
    }
}
?>