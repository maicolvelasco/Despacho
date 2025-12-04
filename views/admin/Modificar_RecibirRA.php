<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Observaciones</title>
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS y Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 70px;
            margin-bottom: 60px;
        }
        .linea-delgada {
            border-top: 1px solid #dee2e6;
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
        }
        .icon-label {
            margin-right: 5px;
            color: #0d6efd; /* Color primario de Bootstrap */
        }
        /* Estilos para los botones flotantes */
        .btn-flotante {
            position: fixed;
            bottom: 80px;
            right: 20px;
            z-index: 1000;
        }
        /* Estilo para el botón deshabilitado */
        .btn-disabled {
            pointer-events: none;
            opacity: 0.6;
        }
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

<?php require_once '../../controllers/admin/ModificarRecibirAController.php'; ?>

<body class="bg-light" style="font-size: 0.80em;">
<nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand btn" href="Control.php">
            <i class="bi bi-reply" style="color: white; font-size: 1.0em;"></i>
        </a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white user-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                </a>
                <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<!-- Contenedor Principal -->
<div class="container">
    <!-- Mensajes de Éxito/Error -->
    <?php
    if (isset($_SESSION['mensaje'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['mensaje']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
        echo '</div>';
        unset($_SESSION['mensaje']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['error']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
        echo '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <!-- Información General (Solo Lectura) -->
    <div class="card mb-2">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Información General</h5>
            <!-- Primera Fila: Remisión y Fecha -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <p class="mb-1"><i class="bi bi-file-earmark-text icon-label"></i><strong>Remisión:</strong> <?php echo htmlspecialchars($detalle['remision_numero'] ?? ''); ?></p>
                </div>
                <div class="col-md-6 mb-2">
                    <i class="bi bi-calendar-event icon-label"></i><strong>Fecha:</strong>
                    <?php if ($tipo === 'envio'): ?>
                        <?php echo htmlspecialchars(date("d/m/Y", strtotime($detalle['fecha_inicio'] ?? ''))); ?> - <?php echo htmlspecialchars(date("d/m/Y", strtotime($detalle['fecha_fin'] ?? ''))); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars(date("d/m/Y", strtotime($detalle['fecha'] ?? ''))); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="linea-delgada"></div>

            <!-- Segunda Fila: Vendedor, Cliente y Titular -->
            <div class="row">
                <div class="col-md-4 mb-2">
                    <i class="bi bi-person me-2 icon-label"></i><strong>Titular:</strong> <?php echo htmlspecialchars($detalle['titular_nombre'] ?? 'N/A'); ?>
                </div>
                <div class="col-md-4 mb-2">
                    <i class="bi bi-people me-2 icon-label"></i><strong>Cliente:</strong> <?php echo htmlspecialchars($detalle['cliente_nombre'] ?? 'N/A'); ?>
                </div>
                <div class="col-md-4 mb-2">
                    <i class="bi bi-cart4 me-2 icon-label"></i><strong>Vendedor:</strong> <?php echo htmlspecialchars($usuario_nombre ?? ''); ?>
                </div>
            </div>
            <div class="linea-delgada"></div>

            <!-- Tercera Fila: Conductor y Tipo -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <i class="bi bi-truck me-2 icon-label"></i><strong>Conductor:</strong> <?php echo htmlspecialchars($detalle['conductor'] ?? ''); ?>
                </div>
                <div class="col-md-6 mb-2">
                    <i class="bi bi-key me-2 icon-label"></i><strong>Tipo:</strong>
                    <?php 
                        echo htmlspecialchars($tipo === 'envio' ? ucfirst($detalle['tipo_envio'] ?? '') : ucfirst($detalle['tipo_recibir'] ?? ''));
                    ?>
                </div>
            </div>

            <!-- Cuarta Fila: Transporte y Placa -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <i class="bi bi-truck-flatbed me-2 icon-label"></i><strong>Transporte:</strong> <?php echo htmlspecialchars($detalle['transporte_nombre'] ?? ''); ?>
                </div>
                <div class="col-md-6 mb-2">
                    <i class="bi bi-1-square me-2 icon-label"></i><strong>Placa:</strong> <?php echo htmlspecialchars($detalle['placa'] ?? ''); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pallets Asociados (Editable) -->
    <div class="card mb-2">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-box-seam me-2"></i>Pallets Asociados</h5>
            <form action="../../controllers/admin/ModificarRecibirAController.php" method="POST" id="modificarRecibirForm">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><i class="bi bi-boxes me-1"></i>Pallets</th>
                            <th><i class="bi bi-stack me-1"></i>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pallets as $pallet): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pallet['tamano'] ?? ''); ?></td>
                                <td>
                                    <input type="hidden" name="pallets[<?php echo htmlspecialchars($pallet['pallet_id']); ?>][id]" value="<?php echo htmlspecialchars($pallet['pallet_id']); ?>">
                                    <input style="font-size: 0.96em;" type="number" name="pallets[<?php echo htmlspecialchars($pallet['pallet_id']); ?>][cantidad]" class="form-control" min="0" value="<?php echo htmlspecialchars($pallet['cantidad'] ?? '0'); ?>" required>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Observación (Editable) -->
                <div class="mb-2">
                    <label style="font-size: 1.48em;" for="observacion" class="form-label"><i class="bi bi-journal-text me-2"></i><strong>Observación:</strong></label>
                    <textarea style="font-size: 0.96em;" name="observacion" id="observacion" class="form-control" maxlength="1000" rows="5" placeholder="Escribe una observación (máximo 1000 caracteres)"><?php echo htmlspecialchars($observacion ?? ''); ?></textarea>
                </div>
            </form>
        </div>
    </div>

    <!-- Observación (Solo Lectura) -->
    <?php if (!empty($observacion)): ?>
        <div class="card mb-2">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-chat-dots me-2"></i>Observación</h5>
                <p><?php echo nl2br(htmlspecialchars($observacion)); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Imágenes del Proceso -->
    <div class="card mb-2">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-images me-2"></i>Imágenes del Proceso</h5>
            <?php if (count($imagenes) > 0): ?>
                <div id="carouselImages" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                    <!-- Indicadores del carrusel -->
                    <div class="carousel-indicators">
                        <?php foreach($imagenes as $index => $imagen): ?>
                            <button type="button" data-bs-target="#carouselImages" data-bs-slide-to="<?php echo $index; ?>" 
                                    class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                                    aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                    aria-label="Slide <?php echo $index + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Imágenes del carrusel -->
                    <div class="carousel-inner">
                        <?php foreach($imagenes as $index => $imagen): ?>
                            <?php
                                // Verificar si la imagen existe, de lo contrario usar una imagen por defecto
                                if (!empty($imagen['imagen'])) {
                                    $img_src = 'data:image/jpeg;base64,' . base64_encode($imagen['imagen']);
                                } else {
                                    $img_src = '../../src/default.png'; // Ruta a una imagen por defecto
                                }
                            ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo $img_src; ?>" class="d-block w-100 img-fluid" alt="Imagen <?php echo $index + 1; ?>">
                                <?php if (!empty($imagen['descripcion'])): ?>
                                    <div class="carousel-caption d-none d-md-block">
                                        <p><?php echo htmlspecialchars($imagen['descripcion'] ?? ''); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Controles de navegación -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselImages" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselImages" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            <?php else: ?>
                <p><i class="bi bi-image-fill text-warning me-2"></i>No hay imágenes asociadas a este registro.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Botón de Guardar (Flotante en la esquina inferior izquierda) -->
<a href="#" onclick="submitGuardarBtn(event);" id="guardarBtn" class="btn btn-outline-primary btn-disabled position-fixed" 
   style="color: black; background-color: white; margin-left: 10px; border: 2px solid blue; bottom: 12px; left: 12px; z-index: 9999;">
    <i class="bi bi-floppy" style="color: blue; font-size: 1.5em;"></i> Guardar
</a>

<!-- Botón de Cancelar -->
<a href="Control.php" class="btn btn-outline-danger position-fixed" style="color: black; background-color: white; margin-right: 10px; border: 2px solid red; bottom: 12px; right: 12px; z-index: 9999;">
    <i class="bi bi-x-lg" style="color: red; font-size: 1.5em;"></i> Cancelar
</a>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Validación Básica de Formulario y Habilitación del Botón Guardar -->
<script>
    // Función para manejar el envío del formulario
    function submitGuardarBtn(event) {
        event.preventDefault(); // Prevenir el comportamiento por defecto del enlace
        const guardarBtn = document.getElementById('guardarBtn');
        if (!guardarBtn.classList.contains('btn-disabled')) {
            document.getElementById('modificarRecibirForm').submit();
        }
    }

    // Función para habilitar/deshabilitar el botón Guardar
    function toggleGuardarBtn() {
        const observacion = document.getElementById('observacion').value.trim();
        const guardarBtn = document.getElementById('guardarBtn');
        if (observacion.length > 0) {
            guardarBtn.classList.remove('btn-disabled');
        } else {
            guardarBtn.classList.add('btn-disabled');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const observacionTextarea = document.getElementById('observacion');
        
        // Verificar el estado inicial del textarea
        toggleGuardarBtn();
        
        // Escuchar los cambios en el textarea
        observacionTextarea.addEventListener('input', toggleGuardarBtn);
    });

    // Validación Básica de Formulario (Ya existente)
    document.getElementById('modificarRecibirForm').addEventListener('submit', function(e) {
        let valid = true;
        let cantidadInputs = document.querySelectorAll('input[type="number"]');
        cantidadInputs.forEach(function(input) {
            if (parseInt(input.value) < 0) {
                valid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('Las cantidades de pallets no pueden ser negativas.');
        }
    });
</script>
</body>
</html>