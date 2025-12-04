<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones</title>
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS y estilos personalizados -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .status-icon { font-size: 2rem; }
        .status-icon.todos { color: gray; }
        .status-icon.observacion { color: gray; }
        .floating-btn {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 80px;
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
        /* Contenedor de los ítems con scroll */
        .items-scrollable {
            max-height: 630px; /* Ajusta la altura según los 7 ítems */
            overflow-y: auto; /* Habilita el scroll vertical */
        }
    </style>
</head>
<body class="bg-light" style="font-size: 0.80em;">
    <!-- Incluir el controlador -->
    <?php require_once '../../controllers/admin/NotificacionesController.php'; ?>

    <!-- Barra de navegación con nombre del usuario logueado y menú desplegable -->
    <nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand btn" href="Control.php">
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

    <!-- Contenido principal -->
    <div class="container" style="margin-top: 70px;">
        <!-- Mensaje de éxito -->
        <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill alert-icon"></i>
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); // Eliminar mensaje de éxito de la sesión ?>
        <?php endif; ?>

        <!-- Botones de Filtro -->
        <div class="btn-group w-100 mb-2" role="group" aria-label="Filtro de Notificaciones">
            <button type="button" class="btn btn-outline-primary btn-filter" onclick="mostrarSeccion('todos')">
                <i class="bi bi-bell me-2"></i>Reciente
            </button>
            <button type="button" class="btn btn-outline-danger btn-filter" onclick="mostrarSeccion('caducado')">
                <i class="bi bi-hourglass-split me-2"></i>Caducado
            </button>
            <button type="button" class="btn btn-outline-secondary btn-filter" onclick="mostrarSeccion('observacion')">
                <i class="bi bi-pencil me-2"></i>Mención
            </button>
        </div>

        <!-- Sección de Recientes -->
        <section id="todos" class="mb-2 items-scrollable">
            <?php if (isset($notificacionesNoModificado) && count($notificacionesNoModificado) > 0): ?>
                <?php foreach ($notificacionesNoModificado as $notificacion): ?>
                    <a href="DetallesA.php?id=<?= htmlspecialchars($notificacion['envio_id']) ?>&tipo=envio" class="text-decoration-none">
                        <div class="notificacion-item" 
                            onclick="<?= $notificacion['es_caducado'] === 'si' ? 'handleCaducidadClick(event, ' . htmlspecialchars($notificacion['envio_id']) . ')' : 'handleClick(event, ' . htmlspecialchars($notificacion['envio_id']) . ')' ?>">
                            <div class="d-flex align-items-center border rounded p-3 mb-1 bg-white shadow-sm justify-content-between">
                                <div class="d-flex align-items-center">
                                    <!-- Mostrar íconos diferentes según el estado -->
                                    <?php if ($notificacion['es_caducado'] === 'si'): ?>
                                        <!-- Ícono para caducado -->
                                        <i class="bi bi-hourglass-split status-icon text-danger me-3"></i>
                                    <?php else: ?>
                                        <!-- Ícono para modificado -->
                                        <i class="bi bi-pencil status-icon todos me-3"></i>
                                    <?php endif; ?>
                                    <div>
                                        <?php if ($notificacion['es_caducado'] !== 'si'): ?>
                                            <p class="mb-0 text-info"><strong>Remisión: <?= htmlspecialchars($notificacion['remision_numero']) ?></strong></p>
                                            <p class="mb-0 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($notificacion['cliente_nombre'] ?? $notificacion['titular_nombre']) ?></p>
                                            <p class="mb-0 text-secondary"><strong>Observación:</strong> <?= htmlspecialchars($notificacion['observacion']) ?></p>
                                            <p class="mb-0 text-secondary"><strong>Cantidad de pallets:</strong> <?= htmlspecialchars($notificacion['cantidad_pallets']) ?></p>
                                        <?php else: ?>
                                            <p class="mb-0 text-danger"><strong>Remisión: <?= htmlspecialchars($notificacion['remision_numero']) ?></strong></p>
                                            <p class="mb-0 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($notificacion['cliente_nombre'] ?? $notificacion['titular_nombre']) ?></p>
                                            <p class="mb-0 text-secondary"><strong>Fecha:</strong> <?= htmlspecialchars($notificacion['fecha_inicio']) ?> - <?= htmlspecialchars($notificacion['fecha_fin']) ?></p>
                                            <p class="mb-0 text-secondary"><strong>Cantidad de pallets:</strong> <?= htmlspecialchars($notificacion['cantidad_pallets']) ?></p>
                                            <p class="mb-0 text-danger"><strong>Advertencia:</strong> <?= htmlspecialchars($notificacion['mensaje_advertencia']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill alert-icon"></i>
                    <div>No hay notificaciones pendientes.</div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Sección de Envíos Caducados -->
        <section id="caducado" class="mb-2 d-none items-scrollable">
            <?php if (isset($notificacionesCaducadas) && count($notificacionesCaducadas) > 0): ?>
                <?php foreach ($notificacionesCaducadas as $caducado): ?>
                    <a href="DetallesA.php?id=<?= htmlspecialchars($caducado['envio_id']) ?>&tipo=envio" class="text-decoration-none">
                        <div class="notificacion-item">
                            <div class="d-flex align-items-center border rounded p-3 mb-1 bg-white shadow-sm">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-hourglass-split status-icon me-3 text-danger"></i>
                                    <div>
                                        <p class="mb-0 text-danger"><strong>Remisión: <?= htmlspecialchars($caducado['remision_numero']) ?></strong></p>
                                        <p class="mb-0 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($caducado['cliente_nombre'] ?? $caducado['titular_nombre']) ?></p>
                                        <p class="mb-0 text-secondary"><strong>Fecha:</strong> <?= htmlspecialchars($caducado['fecha_inicio']) ?> - <?= htmlspecialchars($caducado['fecha_fin']) ?></p>
                                        <p class="mb-0 text-secondary"><strong>Cantidad de pallets:</strong> <?= htmlspecialchars($caducado['cantidad_pallets']) ?></p>
                                        <p class="mb-0 text-danger"><strong>Advertencia:</strong> <?= htmlspecialchars($caducado['mensaje_advertencia']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill alert-icon"></i>
                    <div>No hay envíos caducados.</div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Sección de Observación (modificado = 'si') -->
        <section id="observacion" class="mb-2 d-none items-scrollable">
            <?php if (isset($notificacionesModificado) && count($notificacionesModificado) > 0): ?>
                <?php foreach ($notificacionesModificado as $notificacion): ?>
                    <a href="DetallesA.php?id=<?= htmlspecialchars($notificacion['envio_id']) ?>&tipo=envio" class="text-decoration-none text-dark">
                        <div class="d-flex align-items-center border rounded p-3 mb-1 bg-white shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-pencil status-icon observacion me-3"></i>
                                <div>
                                    <p class="mb-0 text-info"><strong>Remisión: <?= htmlspecialchars($notificacion['remision_numero']) ?></strong></p>
                                    <?php if (!empty($notificacion['cliente_nombre'])): ?>
                                        <p class="mb-0 text-secondary"><strong>Cliente:</strong> <?= htmlspecialchars($notificacion['cliente_nombre']) ?></p>
                                    <?php else: ?>
                                        <p class="mb-0 text-secondary"><strong>Titular:</strong> <?= htmlspecialchars($notificacion['titular_nombre']) ?></p>
                                    <?php endif; ?>
                                    <p class="mb-0 text-secondary"><strong>Observación:</strong> <?= htmlspecialchars($notificacion['observacion']) ?></p>
                                    <p class="mb-0 text-secondary"><strong>Cantidad de pallets:</strong> <?= htmlspecialchars($notificacion['cantidad_pallets']) ?></p>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill alert-icon"></i>
                    <div>No hay observaciones.</div>
                </div>
            <?php endif; ?>
        </section>

    </div>

    <!-- Bootstrap JS y funciones JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /**
         * Mostrar la sección seleccionada y guardar en localStorage
         * @param {string} seccion - 'todos' o 'observacion'
         */
        function mostrarSeccion(seccion) {
            document.getElementById('todos').classList.toggle('d-none', seccion !== 'todos');
            document.getElementById('observacion').classList.toggle('d-none', seccion !== 'observacion');
            document.getElementById('caducado').classList.toggle('d-none', seccion !== 'caducado');
            document.querySelectorAll('.btn-filter').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`button[onclick="mostrarSeccion('${seccion}')"]`).classList.add('active');
            // Guardar la sección seleccionada en localStorage
            localStorage.setItem('seccionSeleccionada', seccion);
        }

        function handleClick(event, id) {
            event.preventDefault();
            console.log(`Intentando marcar como modificado el id: ${id}`);

            fetch('../../controllers/admin/NotificacionesController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'same-origin',
                body: `action=marcar_modificado&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Verificar la respuesta desde el controlador
                if (data.status === 'success') {
                    window.location.href = `DetallesA.php?id=${id}&tipo=envio`;
                } else {
                    alert(data.message || 'No se pudo marcar como modificado');
                }
            })
            .catch(error => console.error('Error en la solicitud AJAX:', error));
        }

        function handleCaducidadClick(event, id) {
            event.preventDefault();
            console.log(`Intentando marcar como caducado el id: ${id}`);

            fetch('../../controllers/admin/NotificacionesController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'same-origin',
                body: `action=marcar_caducado&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Verificar la respuesta desde el controlador
                if (data.status === 'success') {
                    window.location.href = `DetallesA.php?id=${id}&tipo=envio`;
                } else {
                    alert(data.message || 'No se pudo actualizar el estado de caducidad');
                }
            })
            .catch(error => console.error('Error en la solicitud AJAX:', error));
        }


        // Función para cargar la sección seleccionada en la página
        document.addEventListener('DOMContentLoaded', () => {
            const seccionSeleccionada = localStorage.getItem('seccionSeleccionada') || 'todos';
            mostrarSeccion(seccionSeleccionada);
        });
    </script>
</body>
</html>