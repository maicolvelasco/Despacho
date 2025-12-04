<?php
// /controllers/admin/ClienteController.php

require_once '../../config/config.php';
require_once '../../models/ModeloCliente.php';

session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../login.php");
    exit();
}

class ClienteController
{
    public $model;

    public function __construct($pdo)
    {
        $this->model = new ModeloCliente($pdo);
    }

    /**
     * Mostrar todos los clientes.
     *
     * @return array
     */
// /controllers/admin/ClienteController.php

// /controllers/admin/ClienteController.php

public function mostrarClientes($pagina = 1, $limite = 10)
{
    $offset = ($pagina - 1) * $limite; // Calcular el offset
    return $this->model->obtenerTodosClientes($offset, $limite);
}

public function contarClientes()
{
    return $this->model->contarClientes();
}

    /**
     * Obtener todos los titulares para el dropdown.
     *
     * @return array
     */
    public function obtenerTitulares()
    {
        return $this->model->obtenerTitulares();
    }

    /**
     * Registrar un nuevo cliente.
     *
     * @param string $nombre
     * @param string|null $codigo
     * @param int $titular_id
     * @return array
     */
    public function registrarCliente($nombre, $codigo, $titular_id)
    {
        // Validar los datos antes de registrar
        if (empty($nombre) || empty($codigo) || $titular_id <= 0) {
            return ['success' => false, 'error' => 'Datos inválidos para el registro de cliente.'];
        }

        return $this->model->registrarCliente($nombre, $codigo, $titular_id);
    }

    /**
     * Obtener un cliente por su ID.
     *
     * @param int $id
     * @return array|false
     */
    public function obtenerCliente($id)
    {
        return $this->model->obtenerClientePorId($id);
    }

    /**
     * Actualizar un cliente existente.
     *
     * @param int $id
     * @param string $nombre
     * @param string|null $codigo
     * @param int $titular_id
     * @return array
     */
    public function actualizarCliente($id, $nombre, $codigo, $titular_id)
    {
        // Validar los datos antes de actualizar
        if ($id <= 0 || empty($nombre) || empty($codigo) || $titular_id <= 0) {
            return ['success' => false, 'error' => 'Datos inválidos para la actualización de cliente.'];
        }

        return $this->model->actualizarCliente($id, $nombre, $codigo, $titular_id);
    }

    /**
     * Eliminar un cliente.
     *
     * @param int $id
     * @return array
     */
    public function eliminarCliente($id)
    {
        if ($id <= 0) {
            return ['success' => false, 'error' => 'ID de cliente inválido para la eliminación.'];
        }

        return $this->model->eliminarClientePorId($id);
    }
}

// Manejo de la solicitud GET para obtener un cliente específico
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $clienteId = intval($_GET['id']);
    $controller = new ClienteController($pdo);
    $cliente = $controller->obtenerCliente($clienteId);
    
    if ($cliente) {
        echo json_encode($cliente);
    } else {
        echo json_encode(['success' => false, 'error' => 'Cliente no encontrado']);
    }
    exit;
}

// Manejo de la solicitud POST para registrar, actualizar o eliminar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ClienteController($pdo);

    if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de cliente inválido']);
            exit;
        }

        $resultado = $controller->eliminarCliente($id);
        echo json_encode($resultado);
        exit;
    }

    // Acción de actualizar cliente
    if (isset($_POST['action']) && $_POST['action'] === 'actualizar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
        $titular_id = isset($_POST['titular_id']) ? intval($_POST['titular_id']) : 0;

        if ($id <= 0 || empty($nombre) || empty($codigo) || $titular_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            exit;
        }

        $resultado = $controller->actualizarCliente($id, $nombre, $codigo, $titular_id);
        echo json_encode($resultado);
        exit;
    }

    // Acción de registrar cliente
    if (isset($_POST['action']) && $_POST['action'] === 'registrar') {
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
        $titular_id = isset($_POST['titular_id']) ? intval($_POST['titular_id']) : 0;

        $resultado = $controller->registrarCliente($nombre, $codigo, $titular_id);
        echo json_encode($resultado);
        exit;
    }
}
?>