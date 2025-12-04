<!-- /views/despacho/Modificar_Envio.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Envío</title
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS y Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos para el modal de cámara */
        #cameraModal .modal-dialog {
            max-width: 600px;
        }
        #video {
            width: 100%;
            height: auto;
        }
        #capturedImage {
            width: 100%;
            height: auto;
            display: none;
        }

        /* Aplica el tamaño de fuente a todo el contenido excepto el nav y el footer */
        body:not(nav):not(footer),
        .container,
        .container * {
            font-size: 0.97em;
        }

        .linea-delgada {
            border-top: 1px solid #dee2e6;
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .info-label i {
            margin-right: 5px;
            color: #0d6efd; /* Color primario de Bootstrap */
        }
        .card-text {
            margin-bottom: 0.5rem;
        }

        /* Estilo para el switch de Bootstrap 5 */
        .form-switch .form-check-input {
            width: 2em;
            height: 1em;
            margin-left: -2.25em;
        }

        /* Estilos para los iconos en las etiquetas */
        .icon-label {
            margin-right: 5px;
            color: #0d6efd; /* Color primario de Bootstrap */
        }

        /* Estilos para los botones con iconos */
        .btn-icon {
            display: flex;
            align-items: center;
        }
        .btn-icon i {
            margin-right: 5px;
        }

        /* Estilos para las alertas con iconos */
        .alert-icon {
            margin-right: 10px;
            font-size: 1.5rem;
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
<?php require_once '../../controllers/admin/ModificarAController.php'; ?>
<body class="bg-light">
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
<div class="container" style="margin-top: 70px; margin-bottom: 70px;">
    <!-- Mensajes de Error y Éxito -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill alert-icon"></i>
            <div><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        </div>
    <?php endif; ?>

    <form action="Modificar_EnvioA.php?id=<?php echo htmlspecialchars($envio_id); ?>" method="POST" id="modificar_envio_form">
        <!-- Información del Envío (Campos No Editables) -->
        <div class="card mb-2">
            <div class="card-body">
                <h5 class="card-title" style="font-size: 1.23em;">
                    <i class="bi bi-pencil-square me-2"></i>
                    Información del Envío
                </h5>
                
                <!-- Primera Fila: Remisión y Fecha -->
                <div class="row mb-1">
                    <div class="col-5">
                        <p>
                            <span><i class="bi bi-clipboard2 text-primary me-1"></i><strong>Remisión:</strong> <?php echo htmlspecialchars($envio['remision_numero'] ?? 'N/A'); ?></span>
                        </p>
                    </div>
                    <div class="col-7">

                        <p>
                            <span><i class="bi bi-calendar-event text-primary me-1"></i><strong>Fecha:</strong>
                            <?php 
                                echo isset($envio['fecha_inicio']) ? date("d/m/Y", strtotime($envio['fecha_inicio'])) : 'N/A'; 
                                echo isset($envio['fecha_fin']) ? ' - ' . date("d/m/Y", strtotime($envio['fecha_fin'])) : '';
                            ?>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="linea-delgada"></div>

                <!-- Segunda Fila: Titular, Cliente y Vendedor -->
                <div class="row mb-1">
                    <div class="col-12">
                        <p>
                            <span><i class="bi bi-person-fill text-primary me-1"></i><strong>Titular:</strong> <?php echo htmlspecialchars($envio['titular_nombre'] ?? 'N/A'); ?></span>
                        </p>
                    </div>
                    <div class="col-12">      
                        <p>
                            <span><i class="bi bi-people-fill text-primary me-1"></i><strong>Cliente:</strong> <?php echo htmlspecialchars($envio['cliente_nombre'] ?? 'N/A'); ?></span>
                        </p>
                    </div>
                    <div class="col-12">  
                        <p>
                            <span><i class="bi bi-truck text-primary me-1"></i><strong>Vendedor:</strong> <?php echo htmlspecialchars($envio['usuario_nombre'] ?? 'N/A'); ?></span>
                        </p>
                    </div>
                </div>
                <div class="linea-delgada"></div>

                <!-- Tercera Fila: Conductor -->
                <div class="row mb-1">
                    <div class="col-12">
                        <p>
                            <span><i class="bi bi-car-front-fill text-primary me-1"></i><strong>Conductor:</strong> <?php echo htmlspecialchars($envio['conductor'] ?? 'N/A'); ?></span>
                        </p>
                    </div>
                </div>

                <!-- Cuarta Fila: Tipo de Transporte, Placa y Tipo -->
                <div class="row mb-1">
                    <div class="col-4">
                        <p>
                            <span><i class="bi bi-truck-flatbed text-primary me-1"></i><strong>Tipo:</strong> <?php echo htmlspecialchars($envio['transporte_nombre'] ?? 'N/A'); ?></span>
                        </p>
                    </div>
                    <div class="col-4">
                        <p>
                            <span><i class="bi bi-clipboard-data text-primary me-1"></i><strong>Placa:</strong> <?php echo htmlspecialchars($envio['placa'] ?? 'N/A'); ?></span>
                        </p>
                    </div>
                    <div class="col-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="tipo_envio_switch" name="tipo_envio" value="duratranz" <?php echo (isset($envio['tipo']) && $envio['tipo'] === 'duratranz') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="tipo_envio_switch">
                                <?php echo (isset($envio['tipo']) && $envio['tipo'] === 'duratranz') ? 'Duratranz' : 'Propio'; ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pallets Asociados (Editable) -->
        <div class="card mb-2">
            <div class="card-body">
                <h5 class="card-title" style="font-size: 1.23em;">
                    <i class="bi bi-box-seam me-2"></i>
                    Pallets Asociados
                </h5>
                <table class="table table-bordered" id="pallets_table">
                    <thead class="table-light">
                        <tr>
                            <th class="col-5"><i class="bi bi-arrows-move me-1"></i>Pallets</th>
                            <th class="col-3"><i class="bi bi-boxes me-1"></i>Cantidad</th>
                            <th class="col-4 text-center"><i class="bi bi-tools me-1"></i>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($pallets_asociados) && is_array($pallets_asociados)) {
                            foreach ($pallets_asociados as $index => $pallet) {
                                ?>
                                <tr>
                                    <td class="col-6">
                                        <select class="form-select" name="pallets[<?php echo $index; ?>][pallet_id]" required>
                                            <option value="">Seleccione un Pallet</option>
                                            <?php foreach ($palletsDisponibles as $disponible): ?>
                                                <option value="<?php echo $disponible['id']; ?>" <?php echo (isset($pallet['pallet_id']) && $pallet['pallet_id'] == $disponible['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($disponible['tamano']) . " (Stock: " . htmlspecialchars($disponible['stock']) . ")"; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="col-3">
                                        <input type="number" class="form-control" name="pallets[<?php echo $index; ?>][cantidad]" min="1" value="<?php echo htmlspecialchars($pallet['cantidad'] ?? '1'); ?>" required>
                                    </td>
                                    <td class="col-3 text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-pallet-btn" title="Eliminar Pallet">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                            $palletIndex = count($pallets_asociados);
                        } else {
                            ?>
                            <tr>
                                <td class="col-6">
                                    <select class="form-select" name="pallets[0][pallet_id]" required>
                                        <option value="">Seleccione un Pallet</option>
                                        <?php foreach ($palletsDisponibles as $pallet): ?>
                                            <option value="<?php echo $pallet['id']; ?>">
                                                <?php echo htmlspecialchars($pallet['tamano']) . " (Stock: " . htmlspecialchars($pallet['stock']) . ")"; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="col-3">
                                    <input type="number" class="form-control" name="pallets[0][cantidad]" min="1" value="1" required>
                                </td>
                                <td class="col-3 text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-pallet-btn" title="Eliminar Pallet">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                            $palletIndex = 1;
                        }
                        ?>
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="add_pallet_btn" title="Agregar Pallet">
                    <i class="bi bi-plus-circle-fill me-1"></i> Agregar Pallet
                </button>
            </div>
        </div>

        <!-- Imágenes del Envío (Editable) -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title" style="font-size: 1.23em;">
                    <i class="bi bi-images me-2"></i>
                    Imágenes del Envío
                </h5>
                <div id="captured_images_container">
                    <?php
                    // Si hay imágenes existentes, prellenarlas
                    if (!empty($imagenes)) {
                        ?>
                        <!-- Carrusel de Imágenes -->
                        <div id="imagesCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <?php foreach ($imagenes as $index => $imagen_base64): ?>
                                    <button type="button" data-bs-target="#imagesCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                            class="<?php echo ($index === 0) ? 'active' : ''; ?>" 
                                            aria-current="<?php echo ($index === 0) ? 'true' : 'false'; ?>" 
                                            aria-label="Slide <?php echo $index + 1; ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel-inner">
                                <?php foreach ($imagenes as $index => $imagen_base64): ?>
                                    <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                                        <img src="<?php echo 'data:image/jpeg;base64,' . base64_encode($imagen_base64); ?>" class="d-block w-100 img-thumbnail" alt="Imagen <?php echo $index + 1; ?>">
                                        <div class="carousel-caption d-none d-md-block">
                                            <p>Imagen <?php echo $index + 1; ?></p>
                                        </div>
                                        <input type="hidden" name="captured_images[]" value="<?php echo htmlspecialchars('data:image/jpeg;base64,' . base64_encode($imagen_base64)); ?>">
                                        <input type="hidden" name="descripcion_imagen[]" value="Imagen <?php echo $index + 1; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($imagenes) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#imagesCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#imagesCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            <?php endif; ?>
                        </div>
                        <!-- Botón Eliminar Imagen Actual -->
                        <div class="mt-3 text-start">
                            <button type="button" class="btn btn-outline-danger" id="eliminar_imagen_btn" title="Eliminar Imagen">
                                <i class="bi bi-trash me-2"></i> Eliminar Imagen
                            </button>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal para tomar foto -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- Mantener la flexibilidad de tamaño -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera-fill me-2"></i>
                    Tomar Foto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <video id="video" autoplay playsinline style="width: 100%; max-height: 400px;"></video>
                <canvas id="canvas" style="display: none;"></canvas>
                <img id="capturedImage" alt="Imagen Capturada" class="img-thumbnail mt-3" style="width: 100%; max-height: 300px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-outline-primary" id="captureBtn">
                    <i class="bi bi-camera me-1"></i> Capturar Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-white d-flex justify-content-between align-items-center" style="padding: 5px 10px; height: 70px; position: fixed; bottom: 0; width: 100%; z-index: 999">
    <!-- Botón de Guardar alejado 10px de la izquierda -->
    <button type="submit" form="modificar_envio_form" class="btn" style="color: dark; background-color: white; margin-left: 10px; border: 2px solid green;">
        <i class="bi bi-floppy" style="color: green; font-size: 1.5em;"></i> Guardar
    </button>
    
    <!-- Botón Flotante para Tomar Foto en el centro -->
    <button type="button" class="btn btn-outline-secondary rounded-circle position-relative" style="width: 80px; height: 80px; border: 2px solid gray; bottom: 12px; background-color: white;" data-bs-toggle="modal" data-bs-target="#cameraModal" title="Tomar Foto">
        <i class="bi bi-camera" style="font-size: 2.8em;"></i>
    </button>
    
    <!-- Botón de Cancelar alejado 10px de la derecha -->
    <a href="Control.php" class="btn" style="color: dark; background-color: white; margin-right: 10px; border: 2px solid red;">
        <i class="bi bi-x-lg" style="color: red; font-size: 1.5em;"></i> Cancelar
    </a>
</footer>

<!-- Bootstrap JS y otros scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Agregar y eliminar filas de pallets
    let palletIndex = <?php echo isset($palletIndex) ? $palletIndex : 1; ?>; // Inicializar índice para pallets

    document.getElementById('add_pallet_btn').addEventListener('click', function() {
        const tableBody = document.getElementById('pallets_table').getElementsByTagName('tbody')[0];
        const newRow = tableBody.insertRow();

        // Celda para el select de pallet
        const cell1 = newRow.insertCell(0);
        const select = document.createElement('select');
        select.className = 'form-select';
        select.name = `pallets[${palletIndex}][pallet_id]`;
        select.required = true;

        // Opciones del select
        let options = '<option value="">Seleccione un Pallet</option>';
        <?php foreach ($palletsDisponibles as $pallet): ?>
            options += `<option value="<?php echo $pallet['id']; ?>"><?php echo htmlspecialchars($pallet['tamano']) . " (Stock: " . htmlspecialchars($pallet['stock']) . ")"; ?></option>`;
        <?php endforeach; ?>
        select.innerHTML = options;
        cell1.appendChild(select);

        // Celda para la cantidad
        const cell2 = newRow.insertCell(1);
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'form-control';
        input.name = `pallets[${palletIndex}][cantidad]`;
        input.min = '1';
        input.value = '1';
        input.required = true;
        cell2.appendChild(input);

        // Celda para el botón de eliminar
        const cell3 = newRow.insertCell(2);
        cell3.className = 'text-center';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger btn-sm remove-pallet-btn';
        removeBtn.title = 'Eliminar Pallet';
        removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
        cell3.appendChild(removeBtn);

        palletIndex++;
    });

    // Eliminar fila de pallets
    document.getElementById('pallets_table').addEventListener('click', function(e) {
        if (e.target && e.target.closest('.remove-pallet-btn')) {
            const row = e.target.closest('tr');
            row.remove();
        }
    });

    // Botón Eliminar Imagen Actual en la sección de Imágenes
    function setupEliminarImagenBtn() {
        const eliminarImagenBtn = document.getElementById('eliminar_imagen_btn');
        if (eliminarImagenBtn) {
            eliminarImagenBtn.addEventListener('click', function() {
                const carousel = document.querySelector('#imagesCarousel');
                if (!carousel) return;

                const activeItem = carousel.querySelector('.carousel-item.active');
                const indicators = carousel.querySelectorAll('.carousel-indicators button');
                const activeIndex = Array.from(carousel.querySelectorAll('.carousel-item')).indexOf(activeItem);

                if (activeItem) {
                    // Remover el carouselItem
                    activeItem.remove();

                    // Remover el indicador correspondiente
                    if (indicators[activeIndex]) {
                        indicators[activeIndex].remove();
                    }

                    // Reorganizar las descripciones y nombres de inputs
                    const remainingItems = carousel.querySelectorAll('.carousel-item');
                    remainingItems.forEach((item, idx) => {
                        const descripcionInput = item.querySelector('input[name="descripcion_imagen[]"]');
                        if (descripcionInput) {
                            descripcionInput.value = "Imagen " + (idx + 1);
                        }
                        const descripcionP = item.querySelector('.carousel-caption p');
                        if (descripcionP) {
                            descripcionP.textContent = "Imagen " + (idx + 1);
                        }

                        // Actualizar los valores de los inputs de imágenes
                        const imagenInput = item.querySelector('input[name="captured_images[]"]');
                        if (imagenInput) {
                            imagenInput.value = imagenInput.value; // Mantener el valor existente
                        }
                    });

                    // Actualizar la instancia del carrusel
                    if (remainingItems.length > 0) {
                        const newActiveIndex = activeIndex === 0 ? 0 : activeIndex - 1;
                        const newActiveItem = carousel.querySelectorAll('.carousel-item')[newActiveIndex];
                        const newActiveIndicator = carousel.querySelectorAll('.carousel-indicators button')[newActiveIndex];

                        if (newActiveItem) {
                            newActiveItem.classList.add('active');
                            if (newActiveIndicator) {
                                newActiveIndicator.classList.add('active');
                                newActiveIndicator.setAttribute('aria-current', 'true');
                            }
                        }
                    } else {
                        // Si no hay más imágenes, eliminar el carrusel y el botón de eliminar
                        carousel.remove();
                        eliminarImagenBtn.remove();
                    }
                }
            });
        }
    }

    // Inicializar el botón de eliminar si ya existe en el servidor
    document.addEventListener('DOMContentLoaded', function() {
        setupEliminarImagenBtn();

        // Actualizar la etiqueta del switch cuando se cambia
        const tipoSwitch = document.getElementById('tipo_envio_switch');
        if (tipoSwitch) {
            tipoSwitch.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (this.checked) {
                    label.textContent = 'Duratranz';
                } else {
                    label.textContent = 'Propio';
                }
            });
        }
    });

    // Variables globales
    let videoStream = null;

    const videoElement = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    const captureBtn = document.getElementById('captureBtn');
    const cameraModalElement = document.getElementById('cameraModal');
    const cameraModal = new bootstrap.Modal(cameraModalElement);

    // Función para iniciar la cámara con preferencias
    async function iniciarCamara(constraints) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            videoElement.srcObject = stream;
            videoStream = stream;
            videoElement.play();
            console.log("Cámara iniciada con éxito.");
        } catch (err) {
            console.error("Error al iniciar la cámara:", err);
            alert("No se pudo acceder a la cámara. Por favor, revisa los permisos y la conexión.");
        }
    }

    // Iniciar la cámara cuando se abre el modal
    cameraModalElement.addEventListener('shown.bs.modal', function () {
        console.log("Modal de cámara abierto.");
        // Intentar primero con la cámara trasera
        iniciarCamara({ video: { facingMode: { ideal: "environment" } } })
            .catch(function(err) {
                console.warn("Cámara trasera no disponible, intentando con la cámara frontal.", err);
                // Si falla, intentar con la cámara frontal
                return iniciarCamara({ video: { facingMode: "user" } });
            });
    });

    // Detener la cámara cuando se cierra el modal
    cameraModalElement.addEventListener('hidden.bs.modal', function () {
        console.log("Modal de cámara cerrado.");
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
            console.log("Stream de cámara detenido.");
        }
        videoElement.srcObject = null;
        capturedImage.style.display = 'none';
    });

    // Capturar la foto
    captureBtn.addEventListener('click', function() {
        if (!videoStream) {
            alert("La cámara no está activa.");
            return;
        }

        const context = canvas.getContext('2d');
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
        const dataURL = canvas.toDataURL('image/png');
        capturedImage.src = dataURL;
        capturedImage.style.display = 'block';
        console.log("Foto capturada.");

        // Agregar la imagen capturada al carrusel
        const carouselContainer = document.getElementById('captured_images_container');
        let carousel = document.querySelector('#imagesCarousel');

        if (!carousel) {
            // Si el carrusel no existe, crearlo junto con el botón de eliminar
            carouselContainer.innerHTML = `
                <div id="imagesCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#imagesCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    </div>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="${dataURL}" class="d-block w-100 img-thumbnail" alt="Imagen 1">
                            <div class="carousel-caption d-none d-md-block">
                                <p>Imagen 1</p>
                            </div>
                            <input type="hidden" name="captured_images[]" value="${dataURL}">
                            <input type="hidden" name="descripcion_imagen[]" value="Imagen 1">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#imagesCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#imagesCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
                <div class="mt-3 text-start">
                    <button type="button" class="btn btn-outline-danger" id="eliminar_imagen_btn" title="Eliminar Imagen">
                        <i class="bi bi-trash me-2"></i> Eliminar Imagen
                    </button>
                </div>
            `;

            // Configurar el evento para el botón de eliminar
            setupEliminarImagenBtn();
        } else {
            // Si el carrusel ya existe, agregar un nuevo slide
            const carouselIndicators = carousel.querySelector('.carousel-indicators');
            const carouselInner = carousel.querySelector('.carousel-inner');
            const newIndex = carouselInner.children.length;

            // Agregar nuevo indicador
            const newIndicator = document.createElement('button');
            newIndicator.type = 'button';
            newIndicator.setAttribute('data-bs-target', '#imagesCarousel');
            newIndicator.setAttribute('data-bs-slide-to', newIndex);
            newIndicator.setAttribute('aria-label', 'Slide ' + (newIndex + 1));
            carouselIndicators.appendChild(newIndicator);

            // Agregar nueva imagen al carrusel
            const newCarouselItem = document.createElement('div');
            newCarouselItem.classList.add('carousel-item');
            newCarouselItem.innerHTML = `
                <img src="${dataURL}" class="d-block w-100 img-thumbnail" alt="Imagen ${newIndex + 1}">
                <div class="carousel-caption d-none d-md-block">
                    <p>Imagen ${newIndex + 1}</p>
                </div>
                <input type="hidden" name="captured_images[]" value="${dataURL}">
                <input type="hidden" name="descripcion_imagen[]" value="Imagen ${newIndex + 1}">
            `;
            carouselInner.appendChild(newCarouselItem);
        }

        // Limpiar el canvas y cerrar el modal
        canvas.width = 0;
        canvas.height = 0;
        capturedImage.style.display = 'none';
        cameraModal.hide();
    });

    // Validación adicional para el switch de tipo_envio si es necesario
    document.getElementById('modificar_envio_form').addEventListener('submit', function(e) {
        const tipoSwitch = document.getElementById('tipo_envio_switch');
        if (tipoSwitch) {
            // Si el switch está marcado, el valor es 'duratranz', de lo contrario 'propio'
            const tipoValue = tipoSwitch.checked ? 'duratranz' : 'propio';
            // Crear un input oculto para enviar el valor correcto
            let tipoInput = document.querySelector('input[name="tipo_envio_hidden"]');
            if (!tipoInput) {
                tipoInput = document.createElement('input');
                tipoInput.type = 'hidden';
                tipoInput.name = 'tipo_envio';
                this.appendChild(tipoInput);
            }
            tipoInput.value = tipoValue;
        }
    });
</script>
</body>
</html>