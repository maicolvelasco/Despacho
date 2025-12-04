<?php
// views/admin/Fechas.php 

// Incluir el controlador que prepara los datos
require_once '../../controllers/admin/FechasController.php';

// Instanciar el controlador y manejar la solicitud
$controller = new FechasController($pdo);
$controller->handleRequest();

// Extraer variables para la vista
$years = $controller->years;
$selectedYear = $controller->selectedYear;
$months = $controller->months;

// Función para obtener el nombre del mes (si está fuera de la clase)
function getMonthName($monthNumber) {
    $months = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return isset($months[$monthNumber]) ? $months[$monthNumber] : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargas</title>
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* Opcional: Ajusta el espacio entre el label y el select */
        .form-label {
            margin-bottom: 0;
            margin-right: 10px;
            white-space: nowrap;
        }
        .year-select-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
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
    </style>
</head>
<body>
    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand btn" href="admin_dashboard.php">
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

    <!-- Contenido Principal -->
    <div class="container mt-5 pt-4">
        <h4 class="mb-4 text-center">Gestión de Envios</h4>
        <form method="GET" action="">
            <div class="row align-items-center mb-4">
                <!-- Select de Años con Label alineado -->
                <div class="col-5 col-md-4 col-sm-12 mb-2 year-select-container">
                    <label for="yearSelect" class="form-label">Año:</label>
                    <select class="form-select" id="yearSelect" name="year" onchange="this.form.submit()">
                        <option value="">Seleccionar</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= htmlspecialchars($year) ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-7 col-md-8 col-sm-12 mb-2 d-flex flex-wrap justify-content-start gap-2">
                    <!-- Botón "Cliente" que desencadena la exportación -->
                    <a href="?export=clientes&year=<?= htmlspecialchars($selectedYear) ?>" class="btn btn-secondary col-5 col-md-5 col-sm-5">
                        Cliente
                    </a>
                    <!-- Botón "Titular" que desencadena la exportación -->
                    <a href="?export=titulares&year=<?= htmlspecialchars($selectedYear) ?>" class="btn btn-secondary col-5 col-md-5 col-sm-5">
                        Titular
                    </a>
                </div>
            </div>
        </form>

        <?php if ($selectedYear): ?>
            <div class="mt-4">
                <div class="btn-group d-flex flex-wrap" role="group" aria-label="Meses">
                    <?php foreach ($months as $month): ?>
                        <a href="?export=recibir&year=<?= htmlspecialchars($selectedYear) ?>&month=<?= htmlspecialchars($month) ?>" class="btn btn-secondary me-2 mb-2">
                            <i class="bi bi-calendar-event-fill me-1"></i> <?= getMonthName($month) ?>
                        </a>
                    <?php endforeach; ?>
                    <!-- Botón "Todos" que desencadena la exportación múltiple -->
                    <a href="?export=todos&year=<?= htmlspecialchars($selectedYear) ?>" class="btn btn-primary me-2 mb-2">
                        <i class="bi bi-folder-fill me-1"></i> Todos
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>