<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Pallets</title>
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS y Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .linea-delgada {
            border-top: 1px solid #dee2e6;
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
        }
        .card-text {
            margin-bottom: 0.5rem;
        }
        .icon-label {
            margin-right: 5px;
            color: #0d6efd; /* Color primario de Bootstrap */
        }
        /* Ajusta el ancho del menú desplegable */
        .custom-dropdown-menu {
            min-width: 200px; /* Cambia este valor según el tamaño deseado */
            max-width: 250px;
        }
        /* Ajusta el tamaño y estilo del enlace del usuario */
        .user-link {
            font-size: 1.2rem; /* Tamaño del texto */
            padding: 0.1rem 1rem; /* Espaciado */
        }
    </style>
</head>
<body class="bg-light" style="font-size: 0.75em;">
<?php require_once '../../controllers/admin/DetallesAController.php'; ?>
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

<!-- Contenedor Principal -->
<div class="container" style="margin-top: 70px; margin-bottom: 70px;">
    <?php
    if (isset($_SESSION['mensaje'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION['mensaje']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
        echo '</div>';
        unset($_SESSION['mensaje']); // Limpiar el mensaje después de mostrarlo
    }
    ?>
    <!-- Información General -->
    <div class="card mb-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Detalle del Registro
                </h6>
                
                <!-- Botón de Aceptar Recepción -->
                <?php if ($tipo === 'recibir' && $detalle['estado'] === 'en_transito'): ?>
                    <button type="button" class="btn btn-outline-success" onclick="aceptarRecibido(<?php echo $id; ?>)">
                        <i class="bi bi-check-lg me-2"></i>
                    </button>
                <?php endif; ?>
            </div>
            <!-- Primera Fila: Remisión y Fecha -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <p class="mb-1"><i class="bi bi-file-earmark-text icon-label"></i><strong>Remisión:</strong> <?php echo htmlspecialchars($detalle['remision_numero']); ?></p>
                </div>
                <div class="col-md-6 mb-2">
                    <i class="bi bi-calendar-event icon-label"></i><strong>Fecha:</strong>
                    <?php if ($tipo === 'envio'): ?>
                        <?php echo date("d/m/Y", strtotime($detalle['fecha_inicio'])); ?> - <?php echo date("d/m/Y", strtotime($detalle['fecha_fin'])); ?>
                    <?php else: ?>
                        <?php echo date("d/m/Y", strtotime($detalle['fecha'])); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="linea-delgada"></div>

            <!-- Segunda Fila: Titular, Cliente y Vendedor -->
            <div class="row">
                <div class="col-md-4 mb-2">
                    <i class="bi bi-person icon-label"></i><strong>Titular:</strong> <?php echo htmlspecialchars($detalle['titular_nombre'] ?? 'N/A'); ?>
                </div>
                <div class="col-md-4 mb-2">
                    <i class="bi bi-people icon-label"></i><strong>Cliente:</strong> <?php echo htmlspecialchars($detalle['cliente_nombre'] ?? 'N/A'); ?>
                </div>
                <div class="col-md-4 mb-2">
                    <i class="bi bi-cart4 icon-label"></i><strong>Vendedor:</strong> <?php echo htmlspecialchars($usuario_nombre); ?>
                </div>
            </div>
            <div class="linea-delgada"></div>

            <!-- Tercera Fila: Conductor y Tipo -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <i class="bi bi-truck icon-label"></i><strong>Conductor:</strong> <?php echo htmlspecialchars($detalle['conductor']); ?>
                </div>
                <div class="col-md-6 mb-2">
                    <i class="bi bi-key icon-label"></i><strong>Tipo:</strong>
                    <?php 
                        if ($tipo === 'envio') {
                            echo htmlspecialchars($tipo_envio); // 'Propio' o 'Duratranz'
                        } else {
                            echo htmlspecialchars($tipo_recibir); // 'Propio' o 'Duratranz'
                        }
                    ?>
                </div>
            </div>

            <!-- Cuarta Fila: Transporte y Placa -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <i class="bi bi-truck-flatbed icon-label"></i><strong>Transporte:</strong> <?php echo htmlspecialchars($detalle['transporte_nombre']); ?>
                </div>
                <div class="col-md-6 mb-2">
                    <i class="bi bi-1-square icon-label"></i><strong>Placa:</strong> <?php echo htmlspecialchars($detalle['placa']); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pallets Asociados -->
    <div class="card mb-2">
        <div class="card-body">
            <h6 class="card-title"><i class="bi bi-box-seam me-2"></i>Pallets Asociados</h6>
            <?php if (count($pallets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><i class="bi bi-boxes me-1"></i>Pallets</th>
                                <th><i class="bi bi-stack me-1"></i>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pallets as $pallet): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pallet['tamano']); ?></td>
                                    <td><?php echo htmlspecialchars($pallet['cantidad']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>No hay pallets asociados a este registro.</p>
            <?php endif; ?>
        </div>
    </div>

        <!-- Observación (si existe) -->
    <?php
    // Obtener la observación usando el modelo
    $observacion = $detalle['observacion'] ?? '';
    ?>
    <?php if (!empty($observacion)): ?>
        <div class="card mb-2">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-journal-text me-2"></i>Observación</h6>
                <p><?php echo nl2br(htmlspecialchars($observacion)); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Imágenes del Proceso -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title"><i class="bi bi-images me-2"></i>Imágenes del Proceso</h6>
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
                                // Suponiendo que las imágenes están almacenadas como BLOB en la base de datos
                                $img_data = $imagen['imagen'];
                                if ($img_data) {
                                    $img_src = 'data:image/jpeg;base64,' . base64_encode($img_data);
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

<div class="d-flex justify-content-end" style="position: fixed; bottom: 12px; width: 100%; padding: 0 20px; z-index: 1000;">

    <?php if ($tipo === 'envio'): ?>
        <!-- Botón flotante para editar el envío -->
        <a href="Modificar_EnvioA.php?id=<?php echo htmlspecialchars($detalle['envio_id']); ?>" class="btn btn-outline-secondary" style="color: black; background-color: white; border: 2px solid gray; z-index: 9999;">
            <i class="bi bi-pencil" style="color: gray; font-size: 1.5em;"></i> Modificar</i> <!-- Ícono de edición dentro del botón flotante -->
        </a>
    <?php endif; ?>


    <?php if ($tipo === 'recibir' && $detalle['estado'] === 'en_transito'): ?>
        <!-- Botón flotante para editar la recepción -->
        <a href="Modificar_RecibirRA.php?id=<?php echo htmlspecialchars($id); ?>&tipo=recibir" class="btn btn-outline-secondary" style="color: black; background-color: white; border: 2px solid gray;">
            <i class="bi bi-pen" style="color: gray; font-size: 1.5em;"></i> Observacion</i> <!-- Ícono de edición dentro del botón flotante -->
        </a>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
            function aceptarRecibido(id) {
            if (confirm('¿Está seguro de aceptar esta recepción?')) {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=aceptar&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Opcional: Mostrar un mensaje de éxito
                        alert('Recepción aceptada correctamente.');
                        // Recargar la página para actualizar la vista
                        location.reload();
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
</body>
</html>