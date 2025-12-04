<?php
require_once '../../controllers/admin/DashboardController.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .custom-dropdown-menu {
            min-width: 200px; /* Cambia este valor según el tamaño deseado */
            max-width: 250px;
        }
        /* Ajusta el tamaño y estilo del enlace del usuario */
        .user-link {
            font-size: 1.2rem; /* Tamaño del texto */
            padding: 0.5rem 1rem; /* Espaciado */
        }
        /* Estilo para los cuadros del dashboard */
        .dashboard-card {
            display: flex;
            align-items: center;
            padding: 20px;
            border-radius: 8px;
            color: white;
            cursor: pointer; /* Indicador de interactividad */
        }
        .dashboard-icon {
            font-size: 2.5rem;
            margin-right: 15px;
        }
        /* Asegura que los gráficos de torta y líneas ocupen todo el alto de la columna */
        .chart-container {
            height: 100%;
        }
        /* Ajusta el tamaño de los gráficos */
        canvas {
            width: 100% !important;
            height: auto !important;
        }
        /* Estilos para los nuevos cuadros de vendedores */
        .vendedor-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
        }
        .vendedor-card h5 {
            margin-bottom: 10px;
        }
        .vendedor-list {
            list-style: none;
            padding: 0;
            margin-bottom: 10px;
        }
        .vendedor-list li {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        .vendedor-list li:last-child {
            border-bottom: none;
        }
        .vendedor-total {
            font-weight: bold;
            text-align: right;
        }
        /* Estilos para el Modal */
        .modal-header {
            background-color: #0d6efd;
            color: white;
        }
        .modal-title {
            margin: 0 auto;
        }
        .pallet-list {
            list-style: none;
            padding: 0;
        }
        .pallet-list li {
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        .pallet-list li:last-child {
            border-bottom: none;
        }
                .floating-btn {
            position: fixed;
            bottom: 30px;
            left: 85%;
            transform: translateX(-50%);
            width: 65px;
            height: 65px;
            z-index: 9999;
        }
        #floatingButtonIcon {
            transition: transform 0.3s ease;
        }
        
        #floatingButtonIcon.rotated {
            transform: rotate(135deg);
        }
        #floatingDropdown.show {
            background-color: red !important; /* Verde más oscuro cuando está abierto */
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../../src/LOGO ESQUINA WEB ICONO.png" alt="Icono Administrador" width="30" height="30" class="d-inline-block align-text-top me-2">
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

    <div class="container-fluid mt-5 flex-grow-1">
        <div class="row">
            <div class="col-12">
                <!-- Cuadros del Dashboard -->
                <div class="row mt-4">
                    <!-- Total Pallets (Suma total de stock) -->
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="dashboard-card bg-primary" id="totalPalletsCard">
                            <i class="bi bi-box-seam dashboard-icon"></i>
                            <div>
                                <h5>Stock Pallets</h5>
                                <h3><?php echo number_format($totalPallets); ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- Total Enviados (Mes actual) -->
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="dashboard-card bg-success">
                            <i class="bi bi-truck dashboard-icon"></i>
                            <div>
                                <h5>Total Enviados</h5>
                                <h3><?php echo number_format($totalEnviados); ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- Total Recibidos (Mes actual) -->
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="dashboard-card bg-warning text-dark">
                            <i class="bi bi-download dashboard-icon"></i>
                            <div>
                                <h5>Total Recibidos</h5>
                                <h3><?php echo number_format($totalRecibidos); ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- Diferencia Enviados - Recibidos (Remisiones Caducadas Incompletas) -->
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="dashboard-card bg-danger" id="caducadosCard" style="cursor: pointer;">
                            <i class="bi bi-calculator dashboard-icon"></i>
                            <div>
                                <h5>Caducados</h5>
                                <h3><?php echo number_format($contador); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos del Dashboard -->
                <div class="row mt-5">
                    <!-- Columna Izquierda: Enviados y Recibidos -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <!-- Gráfico de Enviados por Departamento (Vertical Bar Chart) -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Pallets Enviados por Departamento</h5>
                                <canvas id="enviadosPorDepartamentoChart"></canvas>
                            </div>
                        </div>
                        <!-- Gráfico de Recibidos por Departamento (Horizontal Bar Chart) -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Pallets Recibidos por Departamento</h5>
                                <canvas id="recibidosPorDepartamentoChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- Columna Derecha: Torta -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <h5 class="card-title">Pallets Enviados por Tamaño</h5>
                                <canvas id="enviadosPorTamanoPalletChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nuevos Cuadros de Vendedores -->
                <div class="row mt-5">
                    <!-- Cuadro de Pallets Enviados por Vendedor -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="vendedor-card">
                            <h5>Cantidad de Pallets Enviados</h5>
                            <ul class="vendedor-list">
                                <?php foreach ($palletsEnviadosPorVendedor as $vendedor): ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($vendedor['vendedor']); ?></span>
                                        <span><?php echo number_format($vendedor['total_enviados']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="vendedor-total">
                                Total Enviados: <?php echo number_format(array_sum(array_column($palletsEnviadosPorVendedor, 'total_enviados'))); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Cuadro de Pallets Recibidos por Vendedor -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="vendedor-card">
                            <h5>Cantidad de Pallets Recibidos</h5>
                            <ul class="vendedor-list">
                                <?php foreach ($palletsRecibidosPorVendedor as $vendedor): ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($vendedor['vendedor']); ?></span>
                                        <span><?php echo number_format($vendedor['total_recibidos']); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="vendedor-total">
                                Total Recibidos: <?php echo number_format(array_sum(array_column($palletsRecibidosPorVendedor, 'total_recibidos'))); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nuevo Gráfico de Líneas Anual -->
                <div class="row mt-5">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Envíos vs Recibidos Anuales</h5>
                                <canvas id="comparacionAnualChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal para Total Pallets -->
    <div class="modal fade" id="totalPalletsModal" tabindex="-1" style="margin-top: 70px;" aria-labelledby="totalPalletsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="totalPalletsModalLabel" class="modal-title"><i class="bi bi-box-seam dashboard-icon"></i>Stock de Pallets</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="palletsForm">
                        <div class="mb-3">
                            <label for="departmentSelect" class="form-label">Pallets de:</label>
                            <select class="form-select" id="departmentSelect" required>
                            </select>
                        </div>
                    </form>
                    <div id="palletsResult" class="mt-4">
                        <!-- Resultados se mostrarán aquí -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Agrega este modal después del modal de Total Pallets -->
    <div class="modal fade" id="totalEnviadosModal" tabindex="-1" style="margin-top: 70px;" aria-labelledby="totalEnviadosModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="totalEnviadosModalLabel" class="modal-title"><i class="bi bi-truck dashboard-icon"></i>Pallets Enviados</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="enviadosForm">
                        <div class="mb-3">
                            <label for="departmentEnviadosSelect" class="form-label">Departamento:</label>
                            <select class="form-select" id="departmentEnviadosSelect" required>
                                <option value="Cochabamba" selected>Cochabamba</option>
                            </select>
                        </div>
                    </form>
                    <div id="enviadosResult" class="mt-4">
                        <!-- Resultados se mostrarán aquí -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Agrega este modal después del modal de Enviados -->
    <div class="modal fade" id="totalRecibidosModal" tabindex="-1" style="margin-top: 70px;" aria-labelledby="totalRecibidosModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="totalRecibidosModalLabel" class="modal-title"><i class="bi bi-download dashboard-icon"></i>Pallets Recibidos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="recibidosForm">
                        <div class="mb-3">
                            <label for="departmentRecibidosSelect" class="form-label">Departamento:</label>
                            <select class="form-select" id="departmentRecibidosSelect" required>
                                <option value="Cochabamba" selected>Cochabamba</option>
                            </select>
                        </div>
                    </form>
                    <div id="recibidosResult" class="mt-4">
                        <!-- Resultados se mostrarán aquí -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante con menú desplegable -->
    <div class="dropdown">
    <a style="bottom: 12px;" class="btn btn-success rounded-circle floating-btn d-flex align-items-center justify-content-center" type="button" id="floatingDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i id="floatingButtonIcon" class="bi bi-plus text-white" style="font-size: 2.8rem;"></i>
    </a>
        <ul class="dropdown-menu p-3 border-0 shadow-lg" aria-labelledby="floatingDropdown">
            <li>
                <a class="dropdown-item d-flex align-items-center" href="Control.php">
                    <span class="fw-semibold me-5">Control</span>
                    <i class="bi bi-house" style="font-size: 1.2rem; color: #eb4444;"></i>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center" href="Fechas.php">
                    <span class="fw-semibold me-5">Reporte</span>
                    <i class="bi bi-file-earmark-spreadsheet" style="font-size: 1.2rem; color: #44d2eb;"></i>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center" href="Ajustes.php">
                    <span class="fw-semibold me-2">Configuracion</span>
                    <i class="bi bi-gear" style="font-size: 1.2rem; color: gray;"></i>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Scripts para los Gráficos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const floatingDropdown = document.getElementById('floatingDropdown');
            const floatingButtonIcon = document.getElementById('floatingButtonIcon');
    
            // Evento cuando el dropdown se abre
            floatingDropdown.addEventListener('show.bs.dropdown', function () {
                floatingButtonIcon.classList.add('rotated');
            });
    
            // Evento cuando el dropdown se cierra
            floatingDropdown.addEventListener('hide.bs.dropdown', function () {
                floatingButtonIcon.classList.remove('rotated');
            });
        });
        // Datos para Enviados por Departamento (Vertical Bar Chart)
        const enviadosPorDepartamentoLabels = <?php 
            $labels = array_map(function($item) { return $item['departamento']; }, $enviadosPorDepartamento);
            echo json_encode($labels); 
        ?>;
        const enviadosPorDepartamentoData = <?php 
            $data = array_map(function($item) { return (int)$item['total_enviados']; }, $enviadosPorDepartamento);
            echo json_encode($data); 
        ?>;

        const ctxEnviadosDept = document.getElementById('enviadosPorDepartamentoChart').getContext('2d');
        new Chart(ctxEnviadosDept, {
            type: 'bar',
            data: {
                labels: enviadosPorDepartamentoLabels,
                datasets: [{
                    label: 'Pallets Enviados',
                    data: enviadosPorDepartamentoData,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)', // Verde
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision:0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Datos para Recibidos por Departamento (Horizontal Bar Chart)
        const recibidosPorDepartamentoLabels = <?php 
            $labels = array_map(function($item) { return $item['departamento']; }, $recibidosPorDepartamento);
            echo json_encode($labels); 
        ?>;
        const recibidosPorDepartamentoData = <?php 
            $data = array_map(function($item) { return (int)$item['total_recibidos']; }, $recibidosPorDepartamento);
            echo json_encode($data); 
        ?>;

        const ctxRecibidosDept = document.getElementById('recibidosPorDepartamentoChart').getContext('2d');
        new Chart(ctxRecibidosDept, {
            type: 'bar',
            data: {
                labels: recibidosPorDepartamentoLabels,
                datasets: [{
                    label: 'Pallets Recibidos',
                    data: recibidosPorDepartamentoData,
                    backgroundColor: 'rgba(255, 193, 7, 0.6)', // Amarillo
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Hace el gráfico horizontal
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision:0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Datos para Enviados por Tamaño de Pallet (Pie Chart)
        const enviadosPorTamanoLabels = <?php 
            $labels = array_map(function($item) { return $item['tamano_pallet']; }, $enviadosPorTamanoPallet);
            echo json_encode($labels); 
        ?>;
        const enviadosPorTamanoData = <?php 
            $data = array_map(function($item) { return (int)$item['total_enviados']; }, $enviadosPorTamanoPallet);
            echo json_encode($data); 
        ?>;

        const ctxEnviadosTamano = document.getElementById('enviadosPorTamanoPalletChart').getContext('2d');
        new Chart(ctxEnviadosTamano, {
            type: 'pie',
            data: {
                labels: enviadosPorTamanoLabels,
                datasets: [{
                    label: 'Pallets Enviados por Tamaño',
                    data: enviadosPorTamanoData,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)', // Azul
                        'rgba(255, 206, 86, 0.6)', // Amarillo Claro
                        'rgba(255, 99, 132, 0.6)', // Rojo
                        'rgba(75, 192, 192, 0.6)', // Verde Agua
                        'rgba(153, 102, 255, 0.6)'  // Morado
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Datos para Comparación Anual de Envíos vs Recibidos (Line Chart)
        const meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
                       "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        const datosAnualesEnviados = <?php 
            // Ordenar los datos por mes
            $enviadosAnuales = [];
            for ($i = 1; $i <= 12; $i++) {
                $enviadosAnuales[] = $datosAnuales['enviados'][$i];
            }
            echo json_encode($enviadosAnuales); 
        ?>;
        const datosAnualesRecibidos = <?php 
            // Ordenar los datos por mes
            $recibidosAnuales = [];
            for ($i = 1; $i <= 12; $i++) {
                $recibidosAnuales[] = $datosAnuales['recibidos'][$i];
            }
            echo json_encode($recibidosAnuales); 
        ?>;

        const ctxComparacionAnual = document.getElementById('comparacionAnualChart').getContext('2d');
        new Chart(ctxComparacionAnual, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Pallets Enviados',
                        data: datosAnualesEnviados,
                        borderColor: 'rgba(40, 167, 69, 1)', // Verde
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Pallets Recibidos',
                        data: datosAnualesRecibidos,
                        borderColor: 'rgba(255, 193, 7, 1)', // Amarillo
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                stacked: false,
                plugins: {
                    title: {
                        display: false,
                        text: 'Comparación Anual de Envíos vs Recibidos'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision:0
                        },
                        title: {
                            display: true,
                            text: 'Cantidad de Pallets'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Meses'
                        }
                    }
                }
            }
        });

        // Ajustar la altura de los gráficos para que no sean demasiado grandes
        document.addEventListener('DOMContentLoaded', function() {
            const enviadosChart = document.getElementById('enviadosPorDepartamentoChart').parentElement;
            const recibidosChart = document.getElementById('recibidosPorDepartamentoChart').parentElement;
            const tortaChart = document.getElementById('enviadosPorTamanoPalletChart').parentElement;
            const comparacionChart = document.getElementById('comparacionAnualChart').parentElement;

            // Define una altura máxima para los contenedores de los gráficos
            enviadosChart.style.height = '300px';
            recibidosChart.style.height = '300px';
            tortaChart.style.height = '300px';
            comparacionChart.style.height = '400px'; // Mayor altura para el gráfico de líneas
        });

        // Scripts para el Modal de Total Pallets
        document.addEventListener('DOMContentLoaded', function() {
            const totalPalletsCard = document.getElementById('totalPalletsCard');
            const totalPalletsModal = new bootstrap.Modal(document.getElementById('totalPalletsModal'), {
                keyboard: false
            });
            const departmentSelect = document.getElementById('departmentSelect');
            const palletsResult = document.getElementById('palletsResult');

            // Función para cargar los departamentos en el select
            function loadDepartments() {
                fetch('/controllers/admin/DashboardController.php?action=get_departments_with_pallets')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Agregar la opción "Todos" al inicio
                            const allOption = document.createElement('option');
                            allOption.value = 'all';
                            allOption.textContent = 'Todos los Pallets';
                            departmentSelect.appendChild(allOption);

                            // Agregar las demás opciones de departamentos
                            data.data.forEach(dept => {
                                const option = document.createElement('option');
                                option.value = dept.id;
                                option.textContent = `Pallets de ${dept.nombre}`;
                                departmentSelect.appendChild(option);
                            });

                            // Seleccionar "Todos" por defecto y cargar los pallets
                            departmentSelect.value = 'all';
                            loadPallets('all');
                        } else {
                            alert('Error al cargar los departamentos.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al cargar los departamentos.');
                    });
            }

            // Función para cargar los pallets según el departamento seleccionado
            function loadPallets(department_id) {
                fetch(`/controllers/admin/DashboardController.php?action=get_pallets_by_department&department_id=${department_id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (data.data.length > 0) {
                                let html = '<h5>Detalles de Pallets</h5><ul class="pallet-list">';
                                data.data.forEach(pallet => {
                                    html += `<li><strong>${pallet.tamano}:</strong> ${pallet.stock_total || pallet.stock} Pallets</li>`;
                                });
                                html += '</ul>';
                                palletsResult.innerHTML = html;
                            } else {
                                palletsResult.innerHTML = '<p>No hay pallets disponibles para este departamento.</p>';
                            }
                        } else {
                            palletsResult.innerHTML = `<p>Error: ${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        palletsResult.innerHTML = '<p>Ocurrió un error al cargar los pallets.</p>';
                    });
            }

            // Evento al hacer clic en el cuadro de Total Pallets
            totalPalletsCard.addEventListener('click', function() {
                // Limpiar select y resultados
                departmentSelect.innerHTML = '';
                palletsResult.innerHTML = '';
                // Cargar departamentos con la opción "Todos"
                loadDepartments();
                // Mostrar modal
                totalPalletsModal.show();
            });

            // Evento al cambiar la selección del departamento
            departmentSelect.addEventListener('change', function() {
                const selectedDeptId = this.value;
                if (selectedDeptId) {
                    loadPallets(selectedDeptId);
                } else {
                    palletsResult.innerHTML = '';
                }
            });
        });
        
            document.addEventListener('DOMContentLoaded', function() {
        const totalEnviadosCard = document.querySelector('.dashboard-card.bg-success');
        const totalEnviadosModal = new bootstrap.Modal(document.getElementById('totalEnviadosModal'), {
            keyboard: false
        });
        const departmentEnviadosSelect = document.getElementById('departmentEnviadosSelect');
        const enviadosResult = document.getElementById('enviadosResult');

        // Función para cargar los departamentos con envíos
        function loadDepartmentsWithEnvios() {
            fetch(`/controllers/admin/DashboardController.php?action=get_enviados_por_departamento_pallets`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Limpiar el select actual
                        departmentEnviadosSelect.innerHTML = '';

                        // Obtener departamentos únicos
                        const departamentos = [...new Set(data.data.map(item => item.departamento))];

                        // Llenar el select con los departamentos
                        departamentos.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept;
                            option.textContent = dept;
                            departmentEnviadosSelect.appendChild(option);
                        });

                        // Seleccionar Cochabamba por defecto
                        departmentEnviadosSelect.value = 'Cochabamba';

                        // Cargar datos de Cochabamba
                        loadEnviadosPorDepartamento('Cochabamba');
                    } else {
                        enviadosResult.innerHTML = '<p>Error al cargar los departamentos.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    enviadosResult.innerHTML = '<p>Ocurrió un error al cargar los departamentos.</p>';
                });
        }

        // Función para cargar los pallets enviados por departamento
        function loadEnviadosPorDepartamento(departamento) {
            fetch(`/controllers/admin/DashboardController.php?action=get_enviados_por_departamento_pallets`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Filtrar por departamento
                        const enviadosFiltrados = data.data.filter(item => item.departamento === departamento);
                        
                        if (enviadosFiltrados.length > 0) {
                            let html = `<h5>Pallets Enviados - ${departamento}</h5><ul class="pallet-list">`;
                            enviadosFiltrados.forEach(pallet => {
                                html += `<li><strong>${pallet.tamano}:</strong> ${pallet.total_enviados} Pallets</li>`;
                            });
                            html += '</ul>';
                            enviadosResult.innerHTML = html;
                        } else {
                            enviadosResult.innerHTML = `<p>No hay pallets enviados para ${departamento}.</p>`;
                        }
                    } else {
                        enviadosResult.innerHTML = '<p>Error al cargar los datos.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    enviadosResult.innerHTML = '<p>Ocurrió un error al cargar los pallets enviados.</p>';
                });
        }

        // Evento al hacer clic en el cuadro de Total Enviados
        totalEnviadosCard.addEventListener('click', function() {
            // Cargar departamentos con envíos
            loadDepartmentsWithEnvios();
            totalEnviadosModal.show();
        });

        // Evento al cambiar la selección del departamento
        departmentEnviadosSelect.addEventListener('change', function() {
            const selectedDept = this.value;
            loadEnviadosPorDepartamento(selectedDept);
        });
    });
    
        document.addEventListener('DOMContentLoaded', function() {
        const totalRecibidosCard = document.querySelector('.dashboard-card.bg-warning');
        const totalRecibidosModal = new bootstrap.Modal(document.getElementById('totalRecibidosModal'), {
            keyboard: false
        });
        const departmentRecibidosSelect = document.getElementById('departmentRecibidosSelect');
        const recibidosResult = document.getElementById('recibidosResult');

        // Función para cargar los departamentos con recibidos
        function loadDepartmentsWithRecibidos() {
            fetch(`/controllers/admin/DashboardController.php?action=get_recibidos_por_departamento_pallets`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Limpiar el select actual
                        departmentRecibidosSelect.innerHTML = '';

                        // Obtener departamentos únicos
                        const departamentos = [...new Set(data.data.map(item => item.departamento))];

                        // Llenar el select con los departamentos
                        departamentos.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept;
                            option.textContent = dept;
                            departmentRecibidosSelect.appendChild(option);
                        });

                        // Seleccionar Cochabamba por defecto
                        departmentRecibidosSelect.value = 'Cochabamba';

                        // Cargar datos de Cochabamba
                        loadRecibidosPorDepartamento('Cochabamba');
                    } else {
                        recibidosResult.innerHTML = '<p>Error al cargar los departamentos.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    recibidosResult.innerHTML = '<p>Ocurrió un error al cargar los departamentos.</p>';
                });
        }

        // Función para cargar los pallets recibidos por departamento
        function loadRecibidosPorDepartamento(departamento) {
            fetch(`/controllers/admin/DashboardController.php?action=get_recibidos_por_departamento_pallets`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Filtrar por departamento
                        const recibidosFiltrados = data.data.filter(item => item.departamento === departamento);
                        
                        if (recibidosFiltrados.length > 0) {
                            let html = `<h5>Pallets Recibidos - ${departamento}</h5><ul class="pallet-list">`;
                            recibidosFiltrados.forEach(pallet => {
                                html += `<li><strong>${pallet.tamano}:</strong> ${pallet.total_recibidos} Pallets</li>`;
                            });
                            html += '</ul>';
                            recibidosResult.innerHTML = html;
                        } else {
                            recibidosResult.innerHTML = `<p>No hay pallets recibidos para ${departamento}.</p>`;
                        }
                    } else {
                        recibidosResult.innerHTML = '<p>Error al cargar los datos.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    recibidosResult.innerHTML = '<p>Ocurrió un error al cargar los pallets recibidos.</p>';
                });
        }

        // Evento al hacer clic en el cuadro de Total Recibidos
        totalRecibidosCard.addEventListener('click', function() {
            // Cargar departamentos con recibidos
            loadDepartmentsWithRecibidos();
            totalRecibidosModal.show();
        });

        // Evento al cambiar la selección del departamento
        departmentRecibidosSelect.addEventListener('change', function() {
            const selectedDept = this.value;
            loadRecibidosPorDepartamento(selectedDept);
        });
    });
    
        document.addEventListener('DOMContentLoaded', function() {
        const caducadosCard = document.getElementById('caducadosCard');

        // Evento al hacer clic en el cuadro de Caducados
        caducadosCard.addEventListener('click', function() {
            // Redirigir a la página deseada
            window.location.href = 'Notificaciones.php'; // Cambia esta ruta a la que desees
        });
    });
    </script>
</body>
</html>