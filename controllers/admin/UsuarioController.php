<?php

require_once '../../config/config.php';
require_once '../../models/ModeloVerUsuario.php';

session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../login.php");
    exit();
}

class UsuarioController
{
    public $model;

    public function __construct($pdo)
    {
        $this->model = new ModeloVerUsuario($pdo);
    }

    public function mostrarUsuarios($tipo = 'todos')
    {
        switch ($tipo) {
            case 'vendedores':
                $usuarios = $this->model->obtenerUsuariosPorRol(2);
                break;
            case 'fabrica':
                $usuarios = $this->model->obtenerUsuariosExceptoRol(2);
                break;
            case 'despacho':
                $usuarios = $this->model->obtenerUsuariosPorRol(3);
                break;
            default:
                $usuarios = $this->model->obtenerTodosUsuarios();
        }
        return $usuarios;
    }

    public function obtenerRoles()
    {
        return $this->model->obtenerRoles();
    }

    public function obtenerDepartamentos()
    {
        return $this->model->obtenerDepartamentos();
    }

    public function registrarUsuario($nombre, $direccion, $codigo, $password, $rol_id, $departamento_id)
    {
        return $this->model->registrarUsuario($nombre, $direccion, $codigo, $password, $rol_id, $departamento_id);
    }

    public function obtenerUsuario($id)
    {
        return $this->model->obtenerUsuarioPorId($id);
    }

    public function actualizarUsuario($id, $nombre, $direccion, $codigo, $password, $rol_id, $departamento_id)
    {
        return $this->model->actualizarUsuario($id, $nombre, $direccion, $codigo, $password, $rol_id, $departamento_id);
    }

    public function eliminarUsuario($id)
    {
        return $this->model->eliminarUsuarioPorId($id);
    }
}

// Manejo de la solicitud GET para obtener un usuario
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $usuarioId = intval($_GET['id']); // Asegura que el ID es un entero
    $controller = new UsuarioController($pdo);
    $usuario = $controller->obtenerUsuario($usuarioId);

    if ($usuario) {
        echo json_encode($usuario);
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
    }
    exit;
}

// Manejo de la solicitud POST para registrar, actualizar o eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new UsuarioController($pdo);

    // Acción de eliminar usuario
    if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        // Validar que el ID es válido
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
            exit;
        }

        // Intentar eliminar el usuario
        $resultado = $controller->eliminarUsuario($id);

        echo json_encode($resultado);
        exit;
    }

    // Acción de actualizar usuario
    if (isset($_POST['action']) && $_POST['action'] === 'actualizar') {
        // Validar y sanitizar los datos recibidos
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
        $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $rol_id = isset($_POST['rol_id']) ? intval($_POST['rol_id']) : 0;
        $departamento_id = isset($_POST['departamento_id']) ? intval($_POST['departamento_id']) : 0;

        // Validar que el ID es válido
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
            exit;
        }

        // Intentar actualizar el usuario
        $resultado = $controller->actualizarUsuario($id, $nombre, $direccion, $codigo, $password, $rol_id, $departamento_id);

        echo json_encode($resultado);
        exit;
    }

    // Acción de registrar usuario
    if (isset($_POST['action']) && $_POST['action'] === 'registrar') {
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
        $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $rol_id = isset($_POST['rol_id']) ? intval($_POST['rol_id']) : 0;
        $departamento_id = isset($_POST['departamento_id']) ? intval($_POST['departamento_id']) : 0;

        $resultado = $controller->registrarUsuario($nombre, $direccion, $codigo, $password, $rol_id, $departamento_id);

        echo json_encode($resultado);
        exit;
    }
}