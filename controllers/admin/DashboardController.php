<?php

require_once '../../config/config.php';
require_once '../../models/ModeloDashboard.php';

session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../login.php");
    exit();
}

// Instanciar el modelo
$dashboardModel = new ModeloDashboard($pdo);

// Manejar solicitudes AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    switch ($_GET['action']) {
        case 'get_departments_with_pallets':
            $departments = $dashboardModel->getDepartmentsWithPallets();
            echo json_encode(['status' => 'success', 'data' => $departments]);
            exit();

        case 'get_pallets_by_department':
            if (isset($_GET['department_id'])) {
                $department_id = $_GET['department_id'];
                if ($department_id === 'all') {
                    $pallets = $dashboardModel->getPalletsAllDepartments();
                    echo json_encode(['status' => 'success', 'data' => $pallets]);
                } elseif (is_numeric($department_id)) {
                    $department_id = intval($department_id);
                    $pallets = $dashboardModel->getPalletsByDepartment($department_id);
                    echo json_encode(['status' => 'success', 'data' => $pallets]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'ID de departamento inválido.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID de departamento no proporcionado.']);
            }
            exit();
            
        case 'get_enviados_por_departamento_pallets':
            $enviados = $dashboardModel->getEnviadosPorDepartamentoPallets();
            echo json_encode(['status' => 'success', 'data' => $enviados]);
            exit();
        
        case 'get_recibidos_por_departamento_pallets':
            $recibidos = $dashboardModel->getRecibidosPorDepartamentoPallets();
            echo json_encode(['status' => 'success', 'data' => $recibidos]);
            exit();
        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida.']);
            exit();
    }
}

// Obtener los datos necesarios para la vista principal
$totalPallets = $dashboardModel->getTotalPallets(); // Total de pallets (sin filtrar por fecha)
$totalEnviados = $dashboardModel->getTotalEnviados(); // Enviados del mes actual
$totalRecibidos = $dashboardModel->getTotalRecibidos(); // Recibidos del mes actual
$diffEnviadosRecibidos = $totalEnviados - $totalRecibidos;

// Obtener datos para los gráficos del mes actual
$enviadosPorDepartamento = $dashboardModel->getEnviadosPorDepartamento();
$recibidosPorDepartamento = $dashboardModel->getRecibidosPorDepartamento();
$enviadosPorTamanoPallet = $dashboardModel->getEnviadosPorTamanoPallet();

$contador = $dashboardModel->getCaducadosContador();

// Obtener datos anuales de envíos y recibidos
$datosAnuales = $dashboardModel->getDatosAnuales();

// Obtener datos para los nuevos cuadros de vendedores
$palletsEnviadosPorVendedor = $dashboardModel->getPalletsEnviadosPorVendedor();
$palletsRecibidosPorVendedor = $dashboardModel->getPalletsRecibidosPorVendedor();
?>