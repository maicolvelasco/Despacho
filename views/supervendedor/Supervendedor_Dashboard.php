<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Supervendedor</title>
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS y estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .status-icon { font-size: 2rem; }
        .status-icon.envios { color: #0d6efd; }
        .status-icon.enTransito { color: #dc3545; }
        .status-icon.recibidos { color: #198754; }
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
        .btn-filter {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-filter i {
            margin-right: 5px;
            font-size: 1.2rem;
        }
        .status-text {
            font-size: 0.9rem;
        }
        .alert-icon {
            margin-right: 10px;
        }
        .custom-dropdown-menu {
            min-width: 200px; /* Cambia este valor según el tamaño deseado */
            max-width: 250px;
            z-index: 2050;
        }
        /* Ajusta el tamaño y estilo del enlace del usuario */
        .user-link {
            font-size: 1.2rem; /* Tamaño del texto */
            padding: 0.5rem 1rem; /* Espaciado */
        }
        /* Cambiar el cursor al pasar por encima del ítem */
        .notificacion-item {
            cursor: pointer;
        }
        /* Opcional: Efecto hover para los ítems */
        .notificacion-item:hover {
            background-color: #f8f9fa;
        }

        /* Clase para fondo rojo muy suave */
        .bg-soft-red {
            background-color: #ffebe6 !important; /* Color rojo claro */
        }

        /* Estilos para el buscador */
        .navbar-search {
            width: 100%;
            max-width: 400px;
        }

        /* Estilos para los cuadros del dashboard */
        .dashboard-card {
            display: flex;
            align-items: center;
            padding: 5px;
            justify-content: center;
            border-radius: 8px;
            color: white;
            cursor: pointer; /* Indicador de interactividad */
        }
        .dashboard-icon {
            font-size: 2.5rem;
            margin-right: 15px;
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

        /* Estilos para el Footer */
        footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 998;
        }
        footer a.btn {
            margin: 0 10px;
        }
        .reverse-icon {
            transform: rotateY(180deg) !important;
            display: inline-block;
        }
        .fixed-top {
            background-color: white;
            z-index: 1030;
        }
    </style>
</head>
<body class="bg-light" style="font-size: 0.75em;">
    <!-- Incluir el controlador -->
    <?php require_once '../../controllers/supervendedor/SupervendedorController.php'; ?>

    <!-- Barra de navegación con nombre del usuario logueado, menú desplegable y buscador -->
    <nav class="navbar navbar-expand navbar-dark bg-dark fixed-top" style="z-index: 10000;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../../src/LOGO ESQUINA WEB ICONO.png" alt="Icono Despacho" width="30" height="30" class="d-inline-block align-text-top me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
                    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Contenedor colapsable -->
            <div class="d-flex align-items-center w-100">
                <!-- Buscador -->
                <form class="d-flex ms-auto me-3 navbar-search">
                    <input class="form-control me-2" type="search" placeholder="Buscar..." aria-label="Buscar" id="searchInput">
                </form>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white user-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"></a>
                        <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item"  href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container" style="margin-top: 80px; margin-bottom: 100px;">
        <!-- Cuadros del Dashboard -->
        <div class="row fixed-top" style="justify-content: center; margin-top: 60px;">
            <!-- Stock Pallets (Suma total de stock del departamento del usuario) -->
            <div class="col-6 col-md-5 col-sm-5 mb-2" style="margin-top: 10px;">
                <div class="dashboard-card bg-primary" id="totalPalletsCard">
                    <i class="bi bi-box-seam dashboard-icon"></i>
                    <div>
                        <h5>Pallets</h5>
                        <h3><?php echo number_format($totalPallets); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Filtro -->
        <div class="bg-white btn-group w-100 mb-2" role="group" aria-label="Filtro de Envíos" style="position: fixed; top: 59px; left: 0; width: 100%; z-index: 1000; padding: 10px 0; margin-top: 100px;">
            <button type="button" class="btn btn-outline-primary btn-filter" onclick="mostrarSeccion('envios')">
                <i class="bi bi-clipboard-check me-2"></i>Envíos
            </button>
            <button type="button" class="btn btn-outline-danger btn-filter" onclick="mostrarSeccion('enTransito')">
                <i class="bi bi-truck me-2"></i>Tránsito <span class="badge bg-danger ms-2" id="count-enTransito"><?php echo isset($enTransitoCount) ? $enTransitoCount : 0; ?></span>
            </button>
            <button type="button" class="btn btn-outline-success btn-filter" onclick="mostrarSeccion('recibidos')">
                <i class="bi bi-buildings me-2"></i>Bodega
            </button>
        </div>
    <div style="margin-top: 230px;">
        <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill alert-icon"></i>
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </div>
            <?php unset($_SESSION['success']); // Eliminar mensaje de éxito de la sesión ?>
            <?php endif; ?>
            
            <!-- Sección de Envíos -->
            <section id="envios" class="mb-4">
                <?php if (isset($envios) && count($envios) > 0): ?>
                    <?php foreach ($envios as $envio): ?>
                        <?php
                            // Crear objetos DateTime para fecha_fin y la fecha actual
                            $fecha_fin = new DateTime($envio['fecha_fin']);
                            $today = new DateTime(); // Fecha y hora actuales
                            $today->setTime(0, 0, 0); // Establecer la hora a 00:00:00 para una comparación de fechas precisa
            
                            // Determinar si fecha_fin es igual o menor a hoy
                            $is_fecha_fin_past_or_today = $fecha_fin <= $today;
            
                            // Asignar clase de fondo según la condición
                            $background_class = $is_fecha_fin_past_or_today ? 'bg-soft-red' : 'bg-white';
                        ?>
                        <a href="DetallesD.php?id=<?php echo htmlspecialchars($envio['envio_id']); ?>&tipo=envio" class="text-decoration-none text-dark envio-item" 
                           data-remision="<?= htmlspecialchars($envio['remision_numero']) ?>" 
                           data-conductor="<?= htmlspecialchars($envio['conductor']) ?>" 
                           data-cliente="<?= htmlspecialchars($envio['cliente_nombre'] ?? $envio['titular_nombre']) ?>" 
                           data-titular="<?= htmlspecialchars($envio['titular_nombre']) ?>">
                            <div class="d-flex align-items-center border rounded p-3 mb-2 shadow-sm <?php echo $background_class; ?>">
                                <div class="d-flex align-items-center">
                                    <?php if ($envio['pallet_department_id'] == 1): ?>
                                        <i class="bi bi-truck-flatbed status-icon envios me-3" title="Departamento Cochabamba"></i>
                                    <?php else: ?>
                                        <i class="bi bi-basket3 status-icon envios me-3" title="Otros Departamentos"></i>
                                    <?php endif; ?>
                                    <div>
                                        <p class="mb-1 text-info"><strong>Remisión: <?= htmlspecialchars($envio['remision_numero']) ?></strong></p>
                                        <p class="mb-1 text-secondary"><strong>Fecha:</strong> <?= htmlspecialchars(date("d/m/Y", strtotime($envio['fecha_inicio']))) ?> - <?= htmlspecialchars(date("d/m/Y", strtotime($envio['fecha_fin']))) ?></p>
                                        <p class="mb-1 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($envio['cliente_nombre'] ?? $envio['titular_nombre']) ?></p>
                                        <p class="mb-0 text-secondary"><strong>Total Pallets:</strong> <?= htmlspecialchars($envio['total_pallets']) ?></p>
                                        <?php if (!empty($envio['departamento_origen']) && $envio['departamento_origen'] !== 'N/A'): ?>
                                            <span class="badge <?= obtenerColorDepartamento($envio['departamento_origen']) ?> ms-2">
                                                Envio de <?= htmlspecialchars($envio['departamento_origen']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Eliminado el Icono de Bandera para la sección "Despacho" -->
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill alert-icon"></i>
                        <div>No se han registrado pallets enviados hasta el momento.</div>
                    </div>
                <?php endif; ?>
            </section>
    
            <!-- Sección En Tránsito -->
            <section id="enTransito" class="mb-4 d-none">
                <?php if (isset($enTransito) && count($enTransito) > 0): ?>
                    <?php foreach ($enTransito as $item): ?>
                        <div class="enTransito-item" 
                             data-remision="<?= htmlspecialchars($item['remision_numero']) ?>" 
                             data-conductor="<?= htmlspecialchars($item['conductor']) ?>" 
                             data-cliente="<?= htmlspecialchars($item['cliente_nombre'] ?? $item['titular_nombre']) ?>" 
                             data-titular="<?= htmlspecialchars($item['titular_nombre']) ?>">
                            <div class="d-flex align-items-center border rounded p-3 mb-2 bg-white shadow-sm justify-content-between">
                                <a href="DetallesD.php?id=<?php echo htmlspecialchars($item['id']); ?>&tipo=<?php echo htmlspecialchars($item['tipo_en_transito']); ?>" class="text-decoration-none text-dark flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['tipo_en_transito'] == 'recibir'): ?>
                                            <i class="bi bi-send status-icon enTransito me-3 reverse-icon"></i>
                                        <?php elseif ($item['tipo_en_transito'] == 'envio'): ?>
                                            <i class="bi bi-truck status-icon enTransito envio-icon me-3"></i>
                                        <?php endif; ?>
                                        <div>
                                            <p class="mb-1 text-info"><strong>Remisión: <?= htmlspecialchars($item['remision_numero']) ?></strong></p>
                                            <?php if (!empty($item['fecha_fin'])): ?>
                                                <p class="mb-1 text-secondary"><strong>Fecha:</strong> <?= htmlspecialchars(date("d/m/Y", strtotime($item['fecha_inicio']))) ?> - <?= htmlspecialchars(date("d/m/Y", strtotime($item['fecha_fin']))) ?></p>
                                            <?php else: ?>
                                                <p class="mb-1 text-secondary"><strong>Fecha:</strong> <?= htmlspecialchars(date("d/m/Y", strtotime($item['fecha_inicio']))) ?></p>
                                            <?php endif; ?>
                                            <p class="mb-1 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($item['cliente_nombre'] ?? $item['titular_nombre']) ?></p>
                                            <p class="mb-0 text-secondary"><strong>Total Pallets:</strong> <?= htmlspecialchars($item['total_pallets']) ?></p>
                                            
                                            <!-- Modificación para mostrar departamento de origen para ambos tipos -->
                                            <?php if (!empty($item['departamento_origen']) && $item['departamento_origen'] !== 'N/A'): ?>
                                                <span class="badge <?= obtenerColorDepartamento($item['departamento_origen']) ?> ms-2">
                                                    <?= $item['tipo_en_transito'] == 'recibir' ? 'Devolucion a ' : 'Envio de '; ?> 
                                                    <?= htmlspecialchars($item['departamento_origen']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                                <div class="ms-auto">
                                    <!-- Mostrar el botón de aceptar para ambos tipos -->
                                    <button type="button" class="btn btn-outline-success me-2" onclick="aceptarEnTransito(<?= htmlspecialchars($item['id']) ?>, '<?= htmlspecialchars($item['tipo_en_transito']) ?>')" title="Aceptar">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                        <div>Actualmente, no se registran pallets en tránsito.</div>
                    </div>
                <?php endif; ?>
            </section>
    
            <!-- Sección de Recibidos -->
            <section id="recibidos" class="mb-4 d-none">
                <?php if (isset($recibirRecibidos) && count($recibirRecibidos) > 0): ?>
                    <?php foreach ($recibirRecibidos as $rec): ?>
                        <a href="DetallesD.php?id=<?php echo htmlspecialchars($rec['recibir_id']); ?>&tipo=recibir" class="text-decoration-none text-dark recibidos-item" 
                           data-remision="<?= htmlspecialchars($rec['remision_numero']) ?>" 
                           data-conductor="<?= htmlspecialchars($rec['conductor']) ?>" 
                           data-cliente="<?= htmlspecialchars($rec['cliente_nombre'] ?? $rec['titular_nombre']) ?>" 
                           data-titular="<?= htmlspecialchars($rec['titular_nombre']) ?>">
                            <div class="d-flex align-items-center border rounded p-3 mb-2 bg-white shadow-sm justify-content-between">
                                <div class="d-flex align-items-center">
                                    <?php if ($rec['pallet_department_id'] == 1): ?>
                                        <i class="bi bi-buildings status-icon recibidos me-3" title="Departamento Cochabamba"></i>
                                    <?php else: ?>
                                        <i class="bi bi-mailbox-flag status-icon recibidos me-3" title="Otros Departamentos"></i>
                                    <?php endif; ?>
                                    <div>
                                        <p class="mb-1 text-info"><strong>Remisión: <?= htmlspecialchars($rec['remision_numero']) ?></strong></p>
                                        <p class="mb-1 text-secondary"><strong>Fecha:</strong> <?= htmlspecialchars(date("d/m/Y", strtotime($rec['fecha']))) ?></p>
                                        <p class="mb-1 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($rec['cliente_nombre'] ?? $rec['titular_nombre']) ?></p>
                                        <p class="mb-0 text-secondary"><strong>Total Pallets:</strong> <?= htmlspecialchars($rec['total_pallets']) ?></p>
                                        <?php if (!empty($rec['departamento_origen']) && $rec['departamento_origen'] !== 'N/A'): ?>
                                            <span class="badge <?= obtenerColorDepartamento($rec['departamento_origen']) ?> ms-2">
                                                Recepcion a <?= htmlspecialchars($rec['departamento_origen']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Icono de bandera -->
                                <div>
                                    <?php 
                                        // Determinar si la remisión está completada
                                        $remision_completada = 0;
                                        if (isset($rec['total_pallets_recibidos_total']) && isset($rec['total_pallets_enviados'])) {
                                            if ($rec['total_pallets_recibidos_total'] >= $rec['total_pallets_enviados']) {
                                                $remision_completada = 1;
                                            }
                                        }
                                    ?>
                                    <?php if ($remision_completada == 1): ?>
                                        <!-- Bandera verde -->
                                        <i class="bi bi-bookmark-check-fill" style="color: green; font-size: 2rem;"></i>
                                    <?php else: ?>
                                        <!-- Bandera roja -->
                                        <i class="bi bi-bookmark-dash-fill" style="color: red; font-size: 2rem;"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill alert-icon"></i>
                        <div>Aún no se ha registrado pallets en fábrica.</div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <!-- Modal para Stock Pallets -->
    <div class="modal fade" id="totalPalletsModal" tabindex="-1" style="margin-top: 70px;" aria-labelledby="totalPalletsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="totalPalletsModalLabel" class="modal-title"><i class="bi bi-box-seam dashboard-icon"></i>Stock de Pallets</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($pallets)): ?>
                        <h5>Detalles de Pallets</h5>
                        <ul class="pallet-list">
                            <?php foreach ($pallets as $pallet): ?>
                                <li><strong><?php echo htmlspecialchars($pallet['tamano']); ?>:</strong> <?php echo number_format($pallet['stock']); ?> Pallets</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No hay pallets disponibles para tu departamento.</p>
                    <?php endif; ?>
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
                <a class="dropdown-item d-flex align-items-center" href="Registro_EnvioD.php">
                    <span class="fw-semibold me-5">Registrar Envio</span>
                    <i class="bi bi-file-earmark-plus" style="font-size: 1.2rem; color: #0d6efd;"></i>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center" href="Registro_RecibirD.php">
                    <span class="fw-semibold me-2">Registrar Devolucion</span>
                    <i class="bi bi-arrow-counterclockwise" style="font-size: 1.2rem; color: #6f42c1;"></i>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalFechas">
                    <span class="fw-semibold me-2">Generar Reporte PDF</span>
                    <i class="bi bi-file-earmark-pdf" style="font-size: 1.2rem; color: #dc3545;"></i>
                </a>
            </li>
        </ul>
    </div>

<!-- Modal -->
<div class="modal fade" id="modalFechas" style="margin-top: 100px;" tabindex="-1" role="dialog" aria-labelledby="modalFechasLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFechasLabel">Seleccionar Fechas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="fechaInicio">Fecha de Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio" required>
                </div>
                <div class="form-group">
                    <label for="fechaFin">Fecha de Finalización</label>
                    <input type="date" class="form-control" id="fechaFin" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-danger" id="btnDescargar">Descargar</button>
            </div>
        </div>
    </div>
</div>
    <!-- Bootstrap JS y funciones JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    
    document.addEventListener('DOMContentLoaded', function() {
        const btnDescargar = document.getElementById('btnDescargar');
        const fechaInicio = document.getElementById('fechaInicio');
        const fechaFin = document.getElementById('fechaFin');

        btnDescargar.addEventListener('click', function() {
            // Validar que se hayan seleccionado ambas fechas
            if (fechaInicio.value && fechaFin.value) {
                // Redirigir a la URL de descarga con los parámetros de fecha
                window.location.href = `generates_pdf.php?fecha_inicio=${fechaInicio.value}&fecha_fin=${fechaFin.value}`;
            } else {
                // Mostrar alerta si no se han seleccionado ambas fechas
                alert('Por favor, selecciona tanto la fecha de inicio como la fecha de finalización.');
            }
        });
    });
        /**
         * Mostrar la sección seleccionada y guardar en localStorage
         * @param {string} seccion - 'envios', 'enTransito' o 'recibidos'
         */
        function mostrarSeccion(seccion) {
            const secciones = ['envios', 'enTransito', 'recibidos'];
            
            secciones.forEach(id => {
                const elem = document.getElementById(id);
                if (elem) {
                    elem.classList.toggle('d-none', seccion !== id);
                } else {
                    console.warn(`Elemento con ID '${id}' no encontrado.`);
                }
            });

            // Actualizar el estado activo de los botones
            document.querySelectorAll('.btn-filter').forEach(btn => btn.classList.remove('active'));
            const botonActivo = document.querySelector(`button[onclick="mostrarSeccion('${seccion}')"]`);
            if (botonActivo) {
                botonActivo.classList.add('active');
            } else {
                console.warn(`Botón para sección '${seccion}' no encontrado.`);
            }

            // Guardar la sección seleccionada en localStorage
            localStorage.setItem('seccionSeleccionada', seccion);
        }

        /**
         * Manejar el clic en un ítem de notificación
         * @param {number} id - ID del envío
         */
        function handleClick(id) {
            // Realizar una solicitud AJAX para marcar 'modificado' como 'si'
            fetch('../../controllers/despacho/NotificacionesController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'same-origin', // Importante para enviar las cookies de sesión
                body: `action=marcar_modificado&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Redirigir a detalles.php después de actualizar
                    window.location.href = `DetallesD.php?id=${id}&tipo=envio`;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al procesar la notificación.');
            });
        }

        /**
         * Mostrar la sección seleccionada al cargar la página
         */
        document.addEventListener('DOMContentLoaded', () => {
            const seccionSeleccionada = localStorage.getItem('seccionSeleccionada') || 'envios';
            mostrarSeccion(seccionSeleccionada);
        });

        function aceptarEnTransito(id, tipo) {
            let mensajeConfirmacion = '¿Está seguro de aceptar este ';
            mensajeConfirmacion += (tipo === 'recibir') ? 'recibo' : 'envío';
            mensajeConfirmacion += '?';
    
            if (confirm(mensajeConfirmacion)) {
                fetch('../../controllers/supervendedor/SupervendedorController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=aceptar&id=${id}&tipo=${tipo}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Opcional: Mostrar un mensaje de éxito
                        alert('Operación aceptada correctamente.');
                        // Recargar la página para actualizar la vista
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al aceptar la operación.');
                });
            }
        }

        // Función para mostrar el modal de Stock Pallets
        function mostrarModalPallets() {
            var myModal = new bootstrap.Modal(document.getElementById('totalPalletsModal'), {
                keyboard: false
            });
            myModal.show();
        }

        /**
         * Implementación del filtro de búsqueda
         */
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();

                // Filtrar Envíos
                const envios = document.querySelectorAll('.envio-item');
                envios.forEach(item => {
                    const remision = item.getAttribute('data-remision').toLowerCase();
                    const conductor = item.getAttribute('data-conductor').toLowerCase();
                    const cliente = item.getAttribute('data-cliente').toLowerCase();
                    const titular = item.getAttribute('data-titular').toLowerCase();

                    if (remision.includes(query) || conductor.includes(query) || cliente.includes(query) || titular.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Filtrar En Tránsito
                const enTransito = document.querySelectorAll('.enTransito-item');
                enTransito.forEach(item => {
                    const remision = item.getAttribute('data-remision').toLowerCase();
                    const conductor = item.getAttribute('data-conductor').toLowerCase();
                    const cliente = item.getAttribute('data-cliente').toLowerCase();
                    const titular = item.getAttribute('data-titular').toLowerCase();

                    if (remision.includes(query) || conductor.includes(query) || cliente.includes(query) || titular.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Filtrar Recibidos
                const recibidos = document.querySelectorAll('.recibidos-item');
                recibidos.forEach(item => {
                    const remision = item.getAttribute('data-remision').toLowerCase();
                    const conductor = item.getAttribute('data-conductor').toLowerCase();
                    const cliente = item.getAttribute('data-cliente').toLowerCase();
                    const titular = item.getAttribute('data-titular').toLowerCase();

                    if (remision.includes(query) || conductor.includes(query) || cliente.includes(query) || titular.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
    <script>
        // Implementación del Modal para Stock Pallets
        document.addEventListener('DOMContentLoaded', function() {
            const totalPalletsCard = document.getElementById('totalPalletsCard');
            const totalPalletsModal = new bootstrap.Modal(document.getElementById('totalPalletsModal'), {
                keyboard: false
            });

            // Evento al hacer clic en el cuadro de Stock Pallets
            totalPalletsCard.addEventListener('click', function() {
                // Mostrar modal
                totalPalletsModal.show();
            });
        });
    </script>
</body>
</html>