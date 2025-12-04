<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Recepción</title>
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
            font-size: 0.96em;
        }

        /* Opcional: Aumentar el espacio entre botones en el footer */
        footer .btn {
            margin: 0 5px;
        }

        /* Asegurar que el footer no se deforme en móviles */
        @media (max-width: 576px) {
            footer .btn {
                margin: 5px 0;
            }
        }
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
include '../../controllers/supervendedor/RegistroRecibirDController.php';
?>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="Supervendedor_Dashboard.php">
            <i class="bi bi-reply" style="color: white; font-size: 1.0em;"></i>
        </a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white user-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"></a>
                <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="../../views/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<!-- Contenedor Principal -->
<div class="container" style="margin-top: 70px; margin-bottom: 60px;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form action="Registro_RecibirD.php" method="POST" id="registrar_recibir_form">
    <!-- Información de Recepción -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-truck me-2"></i>
                Registro de Recepcion
            </h5>

            <!-- Primera fila: Selección de Remisión y Fecha -->
            <div class="row mb-3">
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                        <select class="form-select" id="remision_id" name="remision_id" onchange="window.location.href='Registro_RecibirD.php?remision_id=' + this.value" required>
                            <option value="" disabled selected>Remisión</option>
                            <?php foreach ($remisiones as $remision): ?>
                                <option value="<?php echo $remision['id']; ?>" <?php echo (isset($_POST['remision_id']) && $_POST['remision_id'] == $remision['id']) || (isset($_GET['remision_id']) && $_GET['remision_id'] == $remision['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($remision['numero'] . ' - ' . $remision['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" readonly required>
                    </div>
                </div>
            </div>

            <!-- Segunda fila: Titular y Cliente -->
            <div class="row mb-3">
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="titular_nombre" name="titular_nombre" placeholder="Titular" value="<?php echo isset($titular_nombre) ? htmlspecialchars($titular_nombre) : ''; ?>" readonly>
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-people"></i></span>
                        <input type="text" class="form-control" id="cliente_nombre" name="cliente_nombre" placeholder="Cliente" value="<?php echo isset($cliente_nombre) ? htmlspecialchars($cliente_nombre) : ''; ?>" readonly>
                        <!-- Campo oculto para cliente_id -->
                        <input type="hidden" name="cliente_id" value="<?php echo isset($cliente_id) ? intval($cliente_id) : ''; ?>">
                    </div>
                </div>
            </div>

            <!-- Tercera fila: Selección de Transporte y Placa -->
            <div class="row mb-3">
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-truck-flatbed"></i></span>
                        <select class="form-select" id="transporte_id" name="transporte_id" required>
                            <option value="" disabled selected>Transporte</option>
                            <?php foreach ($transportes as $transporte): ?>
                                <option value="<?php echo $transporte['id']; ?>" <?php echo (isset($transporte_id) && $transporte_id == $transporte['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($transporte['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-1-square"></i></span>
                        <input type="text" class="form-control" id="placa" name="placa" placeholder="Placa" value="<?php echo isset($_POST['placa']) ? htmlspecialchars($_POST['placa']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <!-- Cuarta fila: Conductor y Tipo -->
            <div class="row mb-3">
                <div class="col-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-truck"></i></span>
                        <input type="text" class="form-control" id="conductor" name="conductor" placeholder="Conductor" value="<?php echo isset($_POST['conductor']) ? htmlspecialchars($_POST['conductor']) : ''; ?>" required>
                    </div>
                </div>
                <div class="col-6 d-flex align-items-center">
                    <label class="form-check-label me-2" for="tipo_switch">
                        <i class="bi bi-key me-1"></i>Tipo:
                    </label>
                    <div class="form-check form-switch mt-2">
                        <!-- Campo oculto para enviar 'duratranz' cuando el switch no está activo -->
                        <input type="hidden" name="tipo" value="duratranz">
                        <input class="form-check-input" type="checkbox" id="tipoSwitch" name="tipo" value="propio" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'propio') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="tipoSwitch">
                            <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'propio') ? 'Propio' : 'Duratranz'; ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($palletsRemision)): ?>
    <!-- Pallets Asociados -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-box-seam me-2"></i>
                Pallets Asociados
            </h5>
            <table class="table table-bordered" id="pallets_table">
                <thead class="table-light">
                    <tr>
                        <th class="col-3"><i class="bi bi-boxes me-1"></i>Pallets</th>
                        <th class="col-3"><i class="bi bi-stack me-1"></i>Actual</th>
                        <th class="col-3"><i class="bi bi-truck-flatbed me-1"></i>Devolver</th>
                        <th class="col-4 text-center"><i class="bi bi-box-seam me-1"></i>Restante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($palletsRemision as $index => $pallet): ?>
                        <tr>
                            <td>
                                <input type="hidden" name="pallets[<?php echo $index; ?>][pallet_id]" value="<?php echo $pallet['pallet_id']; ?>">
                                <?php echo htmlspecialchars($pallet['tamano']); ?>
                            </td>
                            <td>
                                <input type="number" class="form-control" value="<?php echo htmlspecialchars($pallet['cantidad']); ?>" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control cantidad-recibir" name="pallets[<?php echo $index; ?>][cantidad]" min="1" max="<?php echo $pallet['cantidad']; ?>" data-cantidad-actual="<?php echo $pallet['cantidad']; ?>" placeholder="Cantidad a Recibir" required>
                            </td>
                            <td>
                                <input type="number" class="form-control cantidad-restante" value="<?php echo $pallet['cantidad']; ?>" readonly>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>Estos pallets son de <?php echo htmlspecialchars($departamentoNombre); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Imágenes de Recepción (Opcional) -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-images me-2"></i>
                Imágenes de Recepción
            </h5>
            <div id="captured_images_container">
                <!-- Aquí se mostrarán las imágenes capturadas -->
            </div>
            <!-- Si deseas agregar funcionalidades para agregar/eliminar imágenes, puedes implementar botones similares al primer formulario -->
        </div>
    </div>
    </form>
</div>

<!-- Modal para tomar foto -->
<div class="modal fade" id="cameraModal" tabindex="-1" style="z-index: 9999" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"> <!-- Clase 'modal-sm' para un modal pequeño -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-camera-fill me-2"></i>Tomar Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <video id="video" autoplay playsinline style="width: 100%; max-height: 400px;"></video> <!-- Limitar la altura del video -->
                <canvas id="canvas" style="display: none;"></canvas>
                <img id="capturedImage" alt="Imagen Capturada" class="img-thumbnail mt-3" style="width: 100%; max-height: 300px;"> <!-- Limitar la altura de la imagen capturada -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Cancelar</button>
                <button type="button" class="btn btn-outline-primary" id="captureBtn"><i class="bi bi-camera me-1"></i> Capturar Foto</button>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-white d-flex justify-content-between align-items-center" style="padding: 5px 10px; height: 70px; position: fixed; bottom: 0; width: 100%; z-index: 998;">
    <!-- Botón de Cancelar con borde rojo y texto negro -->
    <a href="Supervendedor_Dashboard.php" class="btn" style="color: black; background-color: white; margin-right: 10px; border: 2px solid red;">
        <i class="bi bi-x-lg" style="color: red; font-size: 1.5em;"></i> Cancelar
    </a>
    <!-- Botón Flotante para Tomar Foto en el centro -->
    <button type="button" class="btn btn-outline-secondary rounded-circle position-relative" style="width: 80px; height: 80px; bottom: 12px; background-color: white; border: 2px solid gray;" data-bs-toggle="modal" data-bs-target="#cameraModal" title="Tomar Foto">
        <i class="bi bi-camera" style="font-size: 2.8em;"></i>
    </button>

    <!-- Botón de Guardar con borde verde y texto negro -->
    <button type="submit" form="registrar_recibir_form" class="btn" style="color: black; background-color: white; margin-left: 10px; border: 2px solid green;">
        <i class="bi bi-floppy" style="color: green; font-size: 1.5em;"></i> Guardar
    </button>
</footer>

<!-- Bootstrap JS y otros scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Actualizar cantidad restante al cambiar cantidad a recibir
    document.querySelectorAll('.cantidad-recibir').forEach(function(input) {
        input.addEventListener('input', function() {
            const cantidadActual = parseInt(this.dataset.cantidadActual);
            const cantidadRecibir = parseInt(this.value) || 0;
            const cantidadRestante = cantidadActual - cantidadRecibir;
            const cantidadRestanteInput = this.closest('tr').querySelector('.cantidad-restante');
            cantidadRestanteInput.value = cantidadRestante >= 0 ? cantidadRestante : 0;
        });
    });

    // Actualizar el label del switch según su estado
    document.getElementById('tipoSwitch').addEventListener('change', function() {
        const label = document.querySelector('label[for="tipoSwitch"]');
        label.textContent = this.checked ? 'Propio' : 'Duratranz';
    });

    // Funcionalidad para tomar foto
    let videoStream = null;

    const videoElement = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    const captureBtn = document.getElementById('captureBtn');
    const cameraModalElement = document.getElementById('cameraModal');
    const cameraModal = new bootstrap.Modal(cameraModalElement);

    // Función para iniciar la cámara con preferencias
    function iniciarCamara(constraints) {
        return navigator.mediaDevices.getUserMedia(constraints)
            .then(function(stream) {
                videoElement.srcObject = stream;
                videoStream = stream;
                videoElement.play();
            });
    }

    // Iniciar la cámara cuando se abre el modal
    cameraModalElement.addEventListener('shown.bs.modal', function () {
        // Intentar primero con la cámara trasera
        iniciarCamara({ video: { facingMode: { ideal: "environment" } } })
            .catch(function(err) {
                console.warn("Cámara trasera no disponible, intentando con la cámara frontal.", err);
                // Si falla, intentar con la cámara frontal
                return iniciarCamara({ video: { facingMode: "user" } });
            })
            .catch(function(err) {
                alert("Error al acceder a la cámara: " + err);
            });
    });

    // Detener la cámara cuando se cierra el modal
    cameraModalElement.addEventListener('hidden.bs.modal', function () {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
        videoElement.srcObject = null;
        capturedImage.style.display = 'none';
    });

    // Capturar la foto
    captureBtn.addEventListener('click', function() {
        const context = canvas.getContext('2d');
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
        
        // Comprimir la imagen con muy baja calidad
        const dataURL = canvas.toDataURL('image/jpeg', 0.3); // Reducir calidad al 30%
        capturedImage.src = dataURL;
        capturedImage.style.display = 'block';

        // Agregar la imagen capturada al contenedor
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
                    <button type="button" class="btn btn-outline-danger" id="eliminar_imagen_btn">
                        <i class="bi bi-trash"></i> Eliminar Imagen
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

    // Botón Eliminar Imagen Actual
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
    });

    // Manejar cambios en la remisión para llenar Titular y Cliente
    // Dado que la lógica ya está manejada en el controlador y los valores están disponibles,
    // solo se deben mostrar en la vista.
</script>
</body>
</html>