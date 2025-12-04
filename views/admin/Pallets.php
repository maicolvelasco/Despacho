<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pallets</title>
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    /* Ajusta el ancho del menú desplegable */
.custom-dropdown-menu {
    min-width: 200px; /* Cambia este valor según el tamaño deseado */
    max-width: 250px;
}
/* Ajusta el tamaño y estilo del enlace del usuario */
.user-link {
    font-size: 1.2rem; /* Tamaño del texto */
    padding: 0.5rem 1rem; /* Espaciado */
}
        /* Aplica el tamaño de fuente a todo el contenido excepto el nav y el footer */
        body:not(nav):not(footer),
        .container,
        .container * {
            font-size: 0.97em;
        }
</style>
<?php require_once '../../controllers/admin/PalletsController.php'; 
// Añade estas líneas antes de mostrar los pallets
$departamentos = $controller->getDepartamentosConPallets();
$departamento_seleccionado = isset($_GET['departamento_id']) ? $_GET['departamento_id'] : 1; // Por defecto Cochabamba
$pallets = $controller->displayPalletsByDepartamento($departamento_seleccionado);
?>
<nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand btn" href="Ajustes.php">
                <i class="bi bi-reply" style="color: white; font-size: 1.0em;"></i>
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white user-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"></a>
                    <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
<body class="bg-light">
<div class="container my-5">
    <h1 class="text-center" style="margin-top: 70px; font-size: 25px;">Gestión de Pallets</h1>

    <!-- Modal de Registro -->
    <div class="modal fade" style="margin-top: 90px;" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Registrar Nuevo Pallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="palletForm" action="Pallets.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="tamano" class="form-label">Tamaño del Pallet</label>
                            <select class="form-select" id="tamano" name="tamano" required>
                                <option value="">Seleccionar Pallet existente</option>
                                <?php 
                                $palletsSinRepetir = $controller->getPalletsSinRepetir();
                                foreach ($palletsSinRepetir as $pallet): ?>
                                    <option value="<?= htmlspecialchars($pallet['tamano']) ?>">
                                        <?= htmlspecialchars($pallet['tamano']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tamano_nuevo" class="form-label">O crear nuevo tamaño</label>
                            <input type="text" class="form-control" id="tamano_nuevo" name="tamano_nuevo">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Seleccionar Departamentos</label>
                            <?php 
                            // Cambiar esto para obtener TODOS los departamentos
                            $todosLosDepartamentos = $controller->getTodosDepartamentos();
                            foreach ($todosLosDepartamentos as $departamento): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="departamentos[]" 
                                           value="<?= $departamento['id'] ?>" 
                                           id="departamento_<?= $departamento['id'] ?>">
                                    <label class="form-check-label" for="departamento_<?= $departamento['id'] ?>">
                                        <?= htmlspecialchars($departamento['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Registrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mt-5">Inventario de Pallets</h2>
    <div class="mb-3">
        <form method="GET" action="Pallets.php">
            <select name="departamento_id" id="departamento" class="form-select" onchange="this.form.submit()">
                <?php foreach ($departamentos as $departamento): ?>
                    <option value="<?= $departamento['id'] ?>" 
                        <?= $departamento['id'] == $departamento_seleccionado ? 'selected' : '' ?>>
                        <?= htmlspecialchars($departamento['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php if (!empty($pallets)) : ?>
        <?php foreach ($pallets as $pallet) : ?>
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <h4 class="me-auto"><?= htmlspecialchars($pallet['tamano']) ?></h4>
                <div class="input-group me-3" style="width: 150px;">
                    <form action="Pallets.php" method="POST" class="d-inline">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $pallet['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger" name="stock" value="<?= $pallet['stock'] - 1 ?>">
                            <i class="bi bi-dash-circle"></i>
                        </button>
                    </form>
                    
                    <input type="number" class="form-control text-center" value="<?= $pallet['stock'] ?>" onchange="updateStock(<?= $pallet['id'] ?>, this.value)">

                    <form action="Pallets.php" method="POST" class="d-inline">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $pallet['id'] ?>">
                        <button type="submit" class="btn btn-outline-success" name="stock" value="<?= $pallet['stock'] + 1 ?>">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    </form>
                </div>
                <form action="Pallets.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $pallet['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">No hay pallets registrados.</p>
    <?php endif; ?>
</div>

    <!-- Botón flotante con menú desplegable -->
    <div class="dropdown">
    <a style="bottom: 12px; position: fixed; bottom: 30px; left: 85%; transform: translateX(-50%); width: 65px; height: 65px; z-index: 9999;" class="btn btn-success rounded-circle floating-btn d-flex align-items-center justify-content-center" type="button" id="floatingDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i id="floatingButtonIcon" class="bi bi-plus text-white" style="font-size: 2.8rem;"></i>
    </a>
        <ul class="dropdown-menu p-3 border-0 shadow-lg" aria-labelledby="floatingDropdown">
            <li>
                <a class="dropdown-item d-flex align-items-center" href="admin_dashboard.php">
                    <span class="fw-semibold me-5">Principal</span>
                    <i class="bi bi-house" style="font-size: 1.2rem; color: gray;"></i>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#registerModal">
                    <span class="fw-semibold me-5">Registrar</span>
                    <i class="bi bi-stack-overflow" style="font-size: 1.2rem; color: cyan;"></i>
                </a>
            </li>
        </ul>
    </div>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateStock(id, stock) {
        const form = document.createElement('form');
        form.action = 'Pallets.php';
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="stock" value="${stock}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const tamanoSelect = document.getElementById('tamano');
        const tamanoNuevoInput = document.getElementById('tamano_nuevo');
    
        tamanoSelect.addEventListener('change', function() {
            if (this.value) {
                tamanoNuevoInput.disabled = true;
                tamanoNuevoInput.value = '';
            } else {
                tamanoNuevoInput.disabled = false;
            }
        });
    
        tamanoNuevoInput.addEventListener('input', function() {
            if (this.value) {
                tamanoSelect.disabled = true;
                tamanoSelect.selectedIndex = 0;
            } else {
                tamanoSelect.disabled = false;
            }
        });
    });
</script>
</body>
</html>