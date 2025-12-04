<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Nuevo Envío</title>
    <link rel="icon" type="image/png" href="./src/LOGO ESQUINA WEB ICONO.png">
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
        /* Aplica el tamaño de fuente a todo el contenido excepto el nav y el footer */
        body:not(nav):not(footer),
        .container,
        .container * {
            font-size: 0.96em;
        }

        /* Estilos para los iconos junto a las etiquetas */
        .icon-label {
            margin-right: 5px;
            color: #0d6efd; /* Color primario de Bootstrap */
        }

        /* Ajustes para la tabla de pallets */
        .table th i {
            margin-right: 5px;
        }

        /* Estilos para los botones flotantes en el footer */
        .footer-btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-btn i {
            margin-right: 5px;
        }
    </style>
</head>
<?php
include '../../controllers/supervendedor/RegistroDController.php';
?>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="Supervendedor_Dashboard.php">
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

    <form action="Registro_EnvioD.php" method="POST" id="registrar_envio_form">
        <!-- Información del Envío -->
        <div class="card mb-2">
            <div class="card-body">
                <h5 class="card-title" style="font-size: 1.25em">
                    <i class="bi bi-truck me-2"></i>
                    Registro de Envio
                </h5>
                
                <!-- Primera Fila: Remisión y Fecha -->
                <div class="row mb-2">
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                            <input type="text" class="form-control" id="numero_remision" placeholder="N° de Remisión" name="numero_remision" value="<?php echo isset($_POST['numero_remision']) ? htmlspecialchars($_POST['numero_remision']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" readonly required>
                        </div>
                    </div>
                </div>
                
                <!-- Segunda Fila: Código y Nombre del Titular -->
                <div class="row mb-2">
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-123"></i></span>
                            <input list="codigos" class="form-control" id="codigo" name="codigo" placeholder="Código" value="<?php echo isset($_POST['codigo']) ? htmlspecialchars($_POST['codigo']) : ''; ?>" required>
                        </div>
                        <datalist id="codigos">
                            <?php
                            // Combinar códigos de titulares y clientes
                            $all_codigos = [];

                            // Obtener códigos de titulares
                            $stmt = $pdo->query("SELECT codigo FROM titular");
                            $titular_codigos = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                            $all_codigos = array_merge($all_codigos, $titular_codigos);

                            // Obtener códigos de clientes
                            $stmt = $pdo->query("SELECT codigo FROM clientes");
                            $cliente_codigos = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                            $all_codigos = array_merge($all_codigos, $cliente_codigos);

                            // Eliminar duplicados
                            $all_codigos = array_unique($all_codigos);

                            foreach ($all_codigos as $codigo_option):
                            ?>
                                <option value="<?php echo htmlspecialchars($codigo_option); ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <div id="codigoFeedback" class="invalid-feedback">
                            Código no válido.
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="titular_nombre" placeholder="Titular" name="titular_nombre" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- Tercera Fila: Nombre del Cliente y Vendedor -->
                <div class="row mb-2">
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-people"></i></span>
                            <input type="text" class="form-control" id="cliente_nombre" placeholder="Cliente" name="cliente_nombre" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-cart4"></i></span>
                            <input type="text" class="form-control" id="vendedor_nombre" placeholder="Vendedor" name="vendedor_nombre" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- Cuarta Fila: Tipo de Transporte y Placa -->
                <div class="row mb-2">
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-truck-flatbed"></i></span>
                            <select class="form-select" id="transporte_id" name="transporte_id" required>
                                <option value="">Transporte</option>
                                <?php foreach ($transportes as $transporte): ?>
                                    <option value="<?php echo $transporte['id']; ?>" <?php echo (isset($_POST['transporte_id']) && $_POST['transporte_id'] == $transporte['id']) ? 'selected' : ''; ?>>
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
                
                <!-- Quinta Fila: Conductor Tipo (Switch) -->
                <div class="row mb-1">
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
                            <input type="hidden" name="tipo" value="duratranz">
                            <input class="form-check-input" type="checkbox" id="tipoSwitch" name="tipo" value="propio" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'propio') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="tipoSwitch">
                                <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'propio') ? 'Propio' : 'Duratranz'; ?>
                            </label>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Sexta Fila: Seleccion de Departamento -->
                <div class="row mb-1">
                    <div class="col-12">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                            <select class="form-select" id="departamento_select" name="departamento_id" required>
                                <?php
                                // Generar opciones para departamentos con pallets
                                foreach ($departamentosConPallets as $departamento):
                                    $isSelected = ($current_user_department && $departamento['id'] == $current_user_department['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $departamento['id']; ?>" <?php echo $isSelected; ?>>
                                        Bodega <?php echo htmlspecialchars($departamento['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Campos Ocultos para titular_id y cliente_id -->
                <input type="hidden" name="titular_id" id="titular_id">
                <input type="hidden" name="cliente_id" id="cliente_id">
            </div>
        </div>

        <!-- Pallets Asociados (Opcional) -->
        <div class="card mb-2">
            <div class="card-body">
                <h5 class="card-title" style="font-size: 1.24em">
                    <i class="bi bi-box-seam me-2"></i>
                    Pallets Asociados
                </h5>
                <table class="table table-bordered" id="pallets_table">
                    <thead class="table-light">
                        <tr>
                            <th class="col-4"><i class="bi bi-boxes"></i>Pallets</th>
                            <th class="col-3"><i class="bi bi-stack"></i>Cantidad</th>
                            <th class="col-4 text-center"><i class="bi bi-tools"></i>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($pallets) && is_array($pallets)) {
                            foreach ($pallets as $index => $pallet) {
                                ?>
                                <tr>
                                    <td class="col-6">
                                        <select class="form-select" name="pallets[<?php echo $index; ?>][pallet_id]" required>
                                            <option value="">Seleccione un Pallet</option>
                                            <?php foreach ($departamento_pallets[$_POST['departamento_id']] as $disponible): ?>
                                                <option value="<?php echo $disponible['id']; ?>" <?php echo (isset($pallet['pallet_id']) && $pallet['pallet_id'] == $disponible['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($disponible['tamano']) . " (Stock: " . htmlspecialchars($disponible['stock']) . ")"; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="col-3">
                                        <input type="number" class="form-control" name="pallets[<?php echo $index; ?>][cantidad]" min="1" value="<?php echo isset($pallet['cantidad']) ? htmlspecialchars($pallet['cantidad']) : ''; ?>" required>
                                    </td>
                                    <td class="col-3 text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-pallet-btn" title="Eliminar Pallet">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td class="col-6">
                                    <select class="form-select" name="pallets[0][pallet_id]" required>
                                        <option value="">Seleccione un Pallet</option>
                                        <?php foreach ($departamento_pallets[$current_user_department['id']] as $pallet): ?>
                                            <option value="<?php echo $pallet['id']; ?>">
                                                <?php echo htmlspecialchars($pallet['tamano']) . " (Stock: " . htmlspecialchars($pallet['stock']) . ")"; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="col-3">
                                    <input type="number" class="form-control" name="pallets[0][cantidad]" min="1" required>
                                </td>
                                <td class="col-3 text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-pallet-btn" title="Eliminar Pallet">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="add_pallet_btn">
                    <i class="bi bi-plus-circle me-1"></i> Agregar Pallet
                </button>
            </div>
        </div>

        <!-- Imágenes del Envío (Opcional) -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title" style="font-size: 1.24em">
                    <i class="bi bi-images me-2"></i>
                    Imágenes del Envío
                </h5>
                <div id="captured_images_container">
                    <?php
                    // Si el formulario se ha enviado y hay imágenes capturadas, prellenarlas
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($imagenes) && is_array($imagenes)) {
                        ?>
                        <!-- Carrusel de Imágenes -->
                        <div id="imagesCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <?php foreach ($imagenes as $index => $imagen_base64): ?>
                                    <button type="button" data-bs-target="#imagesCarousel" data-bs-slide-to="<?php echo $index; ?>" <?php echo ($index === 0) ? 'class="active"' : ''; ?> aria-current="<?php echo ($index === 0) ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel-inner">
                                <?php foreach ($imagenes as $index => $imagen_base64): ?>
                                    <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                                        <img src="<?php echo $imagen_base64; ?>" class="d-block w-100 img-thumbnail" alt="Imagen <?php echo $index + 1; ?>">
                                        <div class="carousel-caption d-none d-md-block">
                                            <p>Imagen <?php echo $index + 1; ?></p>
                                        </div>
                                        <input type="hidden" name="captured_images[]" value="<?php echo htmlspecialchars($imagen_base64); ?>">
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
                <!-- El botón flotante ahora está en el footer -->
            </div>
        </div>
    </form>
</div>

<!-- Modal para tomar foto -->
<div class="modal fade" id="cameraModal" tabindex="-1" style="z-index: 9999" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"> <!-- Clase 'modal-sm' para un modal pequeño -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera-fill me-2"></i>
                    Tomar Foto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <video id="video" autoplay playsinline></video> <!-- Video para la cámara -->
                <canvas id="canvas" style="display: none;"></canvas>
                <img id="capturedImage" alt="Imagen Capturada" class="img-thumbnail mt-3">
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
<footer class="text-white d-flex justify-content-between align-items-center" style="padding: 5px 10px; height: 70px; position: fixed; bottom: 0; width: 100%; z-index: 998;">

    <!-- Botón de Cancelar con borde rojo y texto negro -->
    <a href="Supervendedor_Dashboard.php" class="btn" style="color: black; margin-right: 10px; border: 2px solid red;">
        <i class="bi bi-x-lg" style="color: red; font-size: 1.5em;"></i> Cancelar
    </a>

    <!-- Botón Flotante para Tomar Foto en el centro -->
    <button type="button" class="btn btn-outline-secondary rounded-circle position-relative" style="width: 80px; height: 80px; bottom: 12px; background-color: white; border: 2px solid gray;" data-bs-toggle="modal" data-bs-target="#cameraModal" title="Tomar Foto">
        <i class="bi bi-camera" style="font-size: 2.8em;"></i>
    </button>

    <!-- Botón de Guardar con borde verde y texto negro -->
    <button type="submit" form="registrar_envio_form" class="btn" style="color: black; margin-left: 10px; border: 2px solid green;">
        <i class="bi bi-floppy" style="color: green; font-size: 1.5em;"></i> Guardar
    </button>
</footer>

<!-- Bootstrap JS y otros scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Preparar datos de pallets por departamento
    const palletsByDepartment = <?php echo json_encode($departamento_pallets); ?>;

    // Obtener el departamento por defecto (usuario logueado)
    let selectedDepartmentId = document.getElementById('departamento_select').value;

    // Función para actualizar los selects de pallets según el departamento seleccionado
    function updatePalletsOptions(departmentId) {
        const palletSelects = document.querySelectorAll('#pallets_table select[name^="pallets"]');
        palletSelects.forEach(select => {
            // Guardar el valor actual seleccionado
            const selectedValue = select.value;
            // Limpiar las opciones actuales
            select.innerHTML = '<option value="">Seleccione un Pallet</option>';
            if (palletsByDepartment[departmentId]) {
                palletsByDepartment[departmentId].forEach(pallet => {
                    const option = document.createElement('option');
                    option.value = pallet.id;
                    option.textContent = `${pallet.tamano} (Stock: ${pallet.stock})`;
                    if (pallet.id == selectedValue) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }
        });
    }

    // Actualizar pallets al cargar la página según el departamento por defecto
    document.addEventListener('DOMContentLoaded', function() {
        updatePalletsOptions(selectedDepartmentId);
    });

    // Manejar cambio en el select de departamento
    document.getElementById('departamento_select').addEventListener('change', function() {
        selectedDepartmentId = this.value;
        updatePalletsOptions(selectedDepartmentId);
    });

    // Agregar y eliminar filas de pallets
    let palletIndex = <?php echo isset($pallets) ? count($pallets) : 1; ?>; // Inicializar índice para pallets

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
        if (palletsByDepartment[selectedDepartmentId]) {
            palletsByDepartment[selectedDepartmentId].forEach(pallet => {
                options += `<option value="${pallet.id}">${pallet.tamano} (Stock: ${pallet.stock})</option>`;
            });
        }
        select.innerHTML = options;
        cell1.appendChild(select);

        // Celda para la cantidad
        const cell2 = newRow.insertCell(1);
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'form-control';
        input.name = `pallets[${palletIndex}][cantidad]`;
        input.min = '1';
        input.required = true;
        cell2.appendChild(input);

        // Celda para el botón de eliminar
        const cell3 = newRow.insertCell(2);
        cell3.className = 'text-center';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger btn-sm remove-pallet-btn';
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

    // Manejar el switch de tipo
    document.getElementById('tipoSwitch').addEventListener('change', function() {
        if (this.checked) {
            this.value = 'propio';
            this.nextElementSibling.textContent = 'Propio';
        } else {
            this.value = 'duratranz';
            this.nextElementSibling.textContent = 'Duratranz';
        }
    });

    // Auto-llenar campos basados en el código ingresado
    document.getElementById('codigo').addEventListener('blur', function() {
        const codigo = this.value.trim();
        if (codigo === '') {
            limpiarCampos();
            return;
        }

        // Realizar solicitud AJAX para obtener datos
        fetch(`../../controllers/supervendedor/RegistroDController.php?action=buscar_codigo&codigo=${encodeURIComponent(codigo)}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    mostrarError(data.error);
                    limpiarCampos();
                } else {
                    if (data.tipo === 'titular') {
                        document.getElementById('titular_id').value = data.titular_id;
                        document.getElementById('cliente_id').value = '';
                        document.getElementById('titular_nombre').value = data.titular_nombre;
                        document.getElementById('cliente_nombre').value = data.cliente_nombre;
                        document.getElementById('vendedor_nombre').value = data.vendedor_nombre;
                    } else if (data.tipo === 'cliente') {
                        document.getElementById('titular_id').value = data.titular_id;
                        document.getElementById('cliente_id').value = data.cliente_id;
                        document.getElementById('titular_nombre').value = data.titular_nombre;
                        document.getElementById('cliente_nombre').value = data.cliente_nombre;
                        document.getElementById('vendedor_nombre').value = data.vendedor_nombre;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError("Error al buscar el código. Inténtalo nuevamente.");
                limpiarCampos();
            });
    });

    function mostrarError(mensaje) {
        const codigoInput = document.getElementById('codigo');
        codigoInput.classList.add('is-invalid');
        const feedback = document.getElementById('codigoFeedback');
        feedback.textContent = mensaje;
    }

    function limpiarCampos() {
        document.getElementById('titular_id').value = '';
        document.getElementById('cliente_id').value = '';
        document.getElementById('titular_nombre').value = '';
        document.getElementById('cliente_nombre').value = '';
        document.getElementById('vendedor_nombre').value = '';
        const codigoInput = document.getElementById('codigo');
        codigoInput.classList.remove('is-invalid');
    }

    // Resetear validación al cambiar el código
    document.getElementById('codigo').addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            this.classList.remove('is-invalid');
        }
    });
</script>
</body>
</html>