<?php
require_once '../../config/config.php';
require_once '../../models/ModeloPallets.php';

session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../login.php");
    exit();
}
class PalletController {
    private $model;

    public function __construct($pdo) {
        $this->model = new ModeloPallets($pdo);
    }

    public function displayPallets() {
        return $this->model->getAllPallets();
    }

    public function updatePalletStock($id, $stock) {
        $this->model->updateStock($id, $stock);
    }

    public function createPallet($tamano, $stock) {
        $this->model->addPallet($tamano, $stock);
    }
    public function deletePallet($id) {
        $this->model->deletePallet($id);
    }
    
    public function displayPalletsByDepartamento($departamento_id) {
        return $this->model->getPalletsByDepartamento($departamento_id);
    }
    
    public function getDepartamentosConPallets() {
        return $this->model->getDepartamentosConPallets();
    }
    
    public function getPalletsSinRepetir() {
        return $this->model->getPalletsSinRepetir();
    }
    
    public function createPalletConDepartamento($tamano, $departamento_id) {
        $this->model->addPalletConDepartamento($tamano, $departamento_id);
    }
    
    public function getTodosDepartamentos() {
        return $this->model->getTodosDepartamentos();
    }
}

// Manejo de acciones
$controller = new PalletController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $tamano = !empty($_POST['tamano_nuevo']) ? $_POST['tamano_nuevo'] : $_POST['tamano'];
                
                // Si se seleccionaron departamentos
                if (isset($_POST['departamentos'])) {
                    foreach ($_POST['departamentos'] as $departamento_id) {
                        $controller->createPalletConDepartamento($tamano, $departamento_id);
                    }
                }
                break;
            case 'update':
                $controller->updatePalletStock($_POST['id'], $_POST['stock']);
                break;
            case 'delete':
                $controller->deletePallet($_POST['id']);
                break;
        }
    }
}

$pallets = $controller->displayPallets();
?>