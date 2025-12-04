<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles</title>
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
        /* Estilos para el botón flotante */
        .btn-flotante {
            position: fixed;
            bottom: 80px;
            right: 20px;
            z-index: 1000;
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
<?php 
// /controllers/vendedor/DetalleVentaController.php
session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Vendedor') {
    header("Location: ../../views/login.php");
    exit();
}

require_once '../../config/config.php';
require_once '../../models/ModeloDetalleV.php';

// Inicializar el modelo
$modeloDetalleV = new ModeloDetalleV($pdo);

// Obtener parámetros GET
if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    echo "Datos inválidos.";
    exit();
}

$id = intval($_GET['id']);
$tipo = $_GET['tipo'];

// Validar el tipo
if ($tipo !== 'envio' && $tipo !== 'recibir') {
    echo "Tipo inválido.";
    exit();
}

// Obtener los detalles generales
$detalle = $modeloDetalleV->getDetalleById($id, $tipo);

if (!$detalle) {
    echo "No se encontró el registro.";
    exit();
}

// Obtener los pallets asociados
$pallets = $modeloDetalleV->getPalletsById($id, $tipo);

// Obtener las imágenes asociadas
$imagenes = $modeloDetalleV->getImagenesByDetalle($id, $tipo);

// Extraer el nombre del vendedor
$usuario_nombre = $detalle['usuario_nombre'] ?? '';
?>
<body class="bg-light" style="font-size: 0.80em;">
<nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="Vendedor_Dashboard.php">
            <i class="bi bi-reply" style="color: white; font-size: 1.0em;"></i>
        </a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white user-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"></a>
                <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="../../views/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<!-- Contenedor Principal -->
<div class="container" style="margin-top: 70px; margin-bottom: 60px;">
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

    <!-- Información General -->
    <div class="card mb-4">
        <div class="card-body position-relative">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Información General</h5>
                
                <!-- Botón de Aceptar Envío -->
                <?php if ($tipo === 'envio' && $detalle['estado'] === 'en_transito'): ?>
                    <button type="button" class="btn btn-outline-success" onclick="aceptarEnvio(<?php echo $id; ?>)">
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

            <!-- Segunda Fila: Vendedor, Cliente y Titular -->
            <div class="row">
                <div class="col-md-4 mb-2">
                    <i class="bi bi-cart4 me-2 icon-label"></i><strong>Vendedor:</strong> <?php echo htmlspecialchars($usuario_nombre); ?>
                </div>
                <div class="col-md-4 mb-2">
                    <i class="bi bi-people-fill me-2 icon-label"></i><strong>Cliente:</strong> <?php echo htmlspecialchars($detalle['cliente_nombre'] ?? 'N/A'); ?>
                </div>
                <div class="col-md-4 mb-2">
                    <i class="bi bi-person-fill me-2 icon-label"></i><strong>Titular:</strong> <?php echo htmlspecialchars($detalle['titular_nombre'] ?? 'N/A'); ?>
                </div>
            </div>
            <div class="linea-delgada"></div>

            <!-- Tercera Fila: Conductor y Tipo -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <i class="bi bi-truck me-2 icon-label"></i><strong>Conductor:</strong> <?php echo htmlspecialchars($detalle['conductor']); ?>
                </div>
                <div class="col-md-6 mb-2">
                    <i class="bi bi-key me-2 icon-label"></i><strong>Tipo:</strong>
                    <?php 
                        echo htmlspecialchars($tipo === 'envio' ? ucfirst($detalle['tipo_envio']) : ucfirst($detalle['tipo_recibir']));
                    ?>
                </div>
            </div>

            <!-- Cuarta Fila: Transporte y Placa -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <i class="bi bi-truck-flatbed me-2 icon-label"></i><strong>Transporte:</strong> <?php echo htmlspecialchars($detalle['transporte_nombre']); ?>
                </div>
                <div class="col-md-6 mb-2">
                    <i class="bi bi-1-square me-2 icon-label"></i><strong>Placa:</strong> <?php echo htmlspecialchars($detalle['placa']); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pallets Asociados -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-box-seam me-2"></i>Pallets Asociados</h5>
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
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-journal-text me-2"></i>Observación</h5>
                <p><?php echo nl2br(htmlspecialchars($observacion)); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Imágenes del Proceso -->
    <div class="card mb-4">
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

<!-- Contenedor de Botones Modificar y Cancelar -->
<div class="d-flex justify-content-end" style="position: fixed; bottom: 12px; width: 100%; padding: 0 20px; z-index: 1000;">
    <!-- Botón de Modificar -->
    <?php if (($tipo === 'envio' && $detalle['estado'] === 'en_transito') || ($tipo === 'envio' && $detalle['estado'] === 'en_transito')): ?>
        <a href="Modificar_Pallets.php?id=<?php echo htmlspecialchars($id); ?>&tipo=<?php echo htmlspecialchars($tipo); ?>" 
           class="btn btn-outline-secondary" 
           style="color: black; background-color: white; border: 2px solid gray;">
            <i class="bi bi-pen" style="color: gray; font-size: 1.5em;"></i> Observaciones
        </a>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function aceptarEnvio(id) {
        if (confirm('¿Estás seguro de aceptar este envío?')) {
            fetch('../../controllers/vendedor/VendedorController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=aceptar_envio&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Envío aceptado exitosamente.');
                    window.location.href = 'Vendedor_Dashboard.php'; // Redirigir al dashboard
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al aceptar el envío.');
            });
        }
    }
</script>
</body>
</html>