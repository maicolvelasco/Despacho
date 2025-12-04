<?php
session_start();
require_once '../config/config.php';
require_once '../models/ModeloUsuario.php';

// Clase controladora para gestionar el inicio de sesión
class LoginController {
    private $modeloUsuario;

    // Constructor: recibe la conexión PDO y crea una instancia del modelo de usuario
    public function __construct($pdo) {
        $this->modeloUsuario = new ModeloUsuario($pdo);
    }

    // Función de login que autentica y redirige según el resultado y rol
    public function login($codigo, $password) {
        $usuario = $this->modeloUsuario->autenticar($codigo, $password);

        if ($usuario) {
            // Si la autenticación es exitosa, se almacenan los datos del usuario en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol_nombre'];

            // Redirige según el rol del usuario
            switch ($usuario['rol_nombre']) {
                case 'Administrador':
                    header("Location: ../views/admin/admin_dashboard.php");
                    break;
                case 'Vendedor':
                    header("Location: ../views/vendedor/Vendedor_Dashboard.php");
                    break;
                case 'Despacho':
                    header("Location: ../views/despacho/Despacho_Dashboard.php");
                    break;
                case 'Supervendedor':
                    header("Location: ../views/supervendedor/Supervendedor_Dashboard.php");
                    break;
                default:
                    $_SESSION['error'] = "Rol no reconocido.";
                    header("Location: ../views/login.php");
                    break;
            }
        } else {
            // Si hay error en la autenticación, almacena el mensaje y redirige al login
            $_SESSION['error'] = "Código o contraseña incorrectos.";
            header("Location: ../views/login.php");
        }
        exit();
    }
}

// Manejo de la solicitud POST desde el formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = $_POST['codigo'] ?? '';
    $password = $_POST['password'] ?? '';

    // Instancia del controlador y llamada al método de login
    $loginController = new LoginController($pdo);
    $loginController->login($codigo, $password);
} else {
    // Si no es una solicitud POST, redirige al login con un mensaje de error
    $_SESSION['error'] = "Método de solicitud inválido.";
    header("Location: ../views/login.php");
    exit();
}