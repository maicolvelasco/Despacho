<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Despacho</title>
    <link rel="icon" type="image/png" href="./src/LOGO ESQUINA WEB ICONO.png">
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
        .btn-filter {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-filter i {
            margin-right: 5px;
            font-size: 1.2rem;
        }
        .btn-group-fixed {
            position: fixed;
            top: 59px; /* Altura de la barra de navegación fija */
            left: 0;
            width: 100%;
            z-index: 1000; /* Asegura que los botones estén por encima del contenido */
            padding: 10px 0; /* Espaciado vertical */
        }
        .status-text {
            font-size: 0.9rem;
        }
        .alert-icon {
            margin-right: 10px;
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
    </style>
</head>
<body class="bg-light" style="font-size: 0.75em;">
    <!-- Incluir el controlador -->
    <?php require_once '../../controllers/despacho/DespachoController.php'; ?>

    <!-- Barra de navegación con nombre del usuario logueado, menú desplegable y buscador -->
    <nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
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
                            <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container" style="margin-top: 130px;">
        <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill alert-icon"></i>
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); // Eliminar mensaje de éxito de la sesión ?>
        <?php endif; ?>
        <!-- Botones de Filtro -->
        <div class=" bg-white btn-group btn-group-fixed w-100 mb-2" role="group" aria-label="Filtro de Envíos">
            <button type="button" class="btn btn-outline-primary btn-filter" onclick="mostrarSeccion('envios')">
                <i class="bi bi-clipboard-check me-2"></i>Envios
            </button>
            <button type="button" class="btn btn-outline-danger btn-filter" onclick="mostrarSeccion('enTransito')">
                <i class="bi bi-truck me-2"></i>Tránsito <span class="badge bg-danger ms-2" id="count-enTransito"><?php echo isset($recibirEnTransito) ? count($recibirEnTransito) : 0; ?></span>
            </button>
            <button type="button" class="btn btn-outline-success btn-filter" onclick="mostrarSeccion('recibidos')">
                <i class="bi bi-buildings me-2"></i>Fabrica
            </button>
        </div>

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
                    <a href="Detalles.php?id=<?php echo htmlspecialchars($envio['envio_id']); ?>&tipo=envio" class="text-decoration-none text-dark envio-item" 
                       data-remision="<?= htmlspecialchars($envio['remision_numero']) ?>" 
                       data-conductor="<?= htmlspecialchars($envio['conductor']) ?>" 
                       data-cliente="<?= htmlspecialchars($envio['cliente_nombre'] ?? $envio['titular_nombre']) ?>" 
                       data-titular="<?= htmlspecialchars($envio['titular_nombre']) ?>">
                        <div class="d-flex align-items-center border rounded p-3 mb-2 shadow-sm <?php echo $background_class; ?>">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clipboard-check status-icon envios me-3"></i>
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
            <?php if (isset($recibirEnTransito) && count($recibirEnTransito) > 0): ?>
                <?php foreach ($recibirEnTransito as $rec): ?>
                    <div class="recibir-item enTransito-item" 
                         data-remision="<?= htmlspecialchars($rec['remision_numero']) ?>" 
                         data-conductor="<?= htmlspecialchars($rec['conductor']) ?>" 
                         data-cliente="<?= htmlspecialchars($rec['cliente_nombre'] ?? $rec['titular_nombre']) ?>" 
                         data-titular="<?= htmlspecialchars($rec['titular_nombre']) ?>">
                        <div class="d-flex align-items-center border rounded p-3 mb-2 bg-white shadow-sm justify-content-between">
                            <a href="Detalles.php?id=<?php echo htmlspecialchars($rec['recibir_id']); ?>&tipo=recibir" class="text-decoration-none text-dark flex-grow-1">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-truck status-icon enTransito me-3"></i>
                                    <div>
                                        <p class="mb-1 text-info"><strong>Remisión: <?= htmlspecialchars($rec['remision_numero']) ?></strong></p>
                                        <p class="mb-1 text-secondary"><strong>Fecha:</strong> <?= htmlspecialchars(date("d/m/Y", strtotime($rec['fecha']))) ?></p>
                                        <p class="mb-1 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($rec['cliente_nombre'] ?? $rec['titular_nombre']) ?></p>
                                        <p class="mb-0 text-secondary"><strong>Total Pallets:</strong> <?= htmlspecialchars($rec['total_pallets']) ?></p>
                                        <?php if (!empty($rec['departamento_origen']) && $rec['departamento_origen'] !== 'N/A'): ?>
                                            <span class="badge <?= obtenerColorDepartamento($rec['departamento_origen']) ?> ms-2">
                                                Devolucion a <?= htmlspecialchars($rec['departamento_origen']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                            <div class="ms-auto">
                                <button type="button" class="btn btn-outline-success me-2" onclick="aceptarRecibido(<?= htmlspecialchars($rec['recibir_id']) ?>)" title="Aceptar Recepción">
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
                    <a href="Detalles.php?id=<?php echo htmlspecialchars($rec['recibir_id']); ?>&tipo=recibir" class="text-decoration-none text-dark recibidos-item" 
                       data-remision="<?= htmlspecialchars($rec['remision_numero']) ?>" 
                       data-conductor="<?= htmlspecialchars($rec['conductor']) ?>" 
                       data-cliente="<?= htmlspecialchars($rec['cliente_nombre'] ?? $rec['titular_nombre']) ?>" 
                       data-titular="<?= htmlspecialchars($rec['titular_nombre']) ?>">
                        <div class="d-flex align-items-center border rounded p-3 mb-2 bg-white shadow-sm justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-buildings status-icon recibidos me-3"></i>
                                <div>
                                    <p class="mb-1 text-info"><strong>Remisión: <?= htmlspecialchars($rec['remision_numero']) ?></strong></p>
                                    <p class="mb-1 text-secondary"><strong>Fecha:</strong> <?= htmlspecialchars(date("d/m/Y", strtotime($rec['fecha']))) ?></p>
                                    <p class="mb-1 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($rec['cliente_nombre'] ?? $rec['titular_nombre']) ?></p>
                                    <p class="mb-0 text-secondary"><strong>Total Pallets:</strong> <?= htmlspecialchars($rec['total_pallets']) ?></p>
                                    <?php if (!empty($rec['departamento_origen']) && $rec['departamento_origen'] !== 'N/A'): ?>
                                        <span class="badge <?= obtenerColorDepartamento($rec['departamento_origen']) ?> ms-2">
                                            Devolucion a <?= htmlspecialchars($rec['departamento_origen']) ?>
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
                    <div>Aún no se ha registrado pallets en fabrica.</div>
                </div>
            <?php endif; ?>
        </section>

    </div>

    <!-- Botón flotante en el centro para agregar nuevo envío -->
    <a href="Registro_Envio.php" style="bottom: 12px;" class="btn btn-success rounded-circle floating-btn d-flex align-items-center justify-content-center">
        <i class="bi bi-file-earmark-plus" style="font-size: 2.2rem;"></i>
    </a>

    <!-- Bootstrap JS y funciones JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                    window.location.href = `detalles.php?id=${id}&tipo=envio`;
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

        function aceptarRecibido(id) {
            if (confirm('¿Está seguro de aceptar esta recepción?')) {
                fetch('../../controllers/despacho/DespachoController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=aceptar&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Mostrar un mensaje de éxito o actualizar la interfaz
                        alert('Recepción aceptada correctamente. El stock ha sido actualizado.');
                        location.reload(); // Recargar la página para reflejar cambios
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al aceptar la recepción.');
                });
            }
        }
    </script>
    <script>
        // Implementación del filtro de búsqueda
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

</body>
</html>