<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Titulares</title>
    <link rel="icon" type="image/png" href="/src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        /* Estilos para el contenedor del elemento de usuario */
        .usuario-item-wrapper {
            position: relative;
            overflow: hidden; /* Oculta el contenido que sobresale del contenedor */
            background-color: #fff; /* Fondo blanco para el contenido principal */
            border-radius: 5px; /* Bordes redondeados */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Sombra sutil */
        }

        .editar-btn, .eliminar-btn {
            display: flex;
            align-items: center;
            gap: 5px; /* Espaciado entre el ícono y el texto */
            padding: 4px 8px; /* Ajusta el padding para mejor apariencia */
            font-size: 0.9em; /* Ajusta el tamaño de la fuente si es necesario */
        }

        .editar-btn i, .eliminar-btn i {
            font-size: 1.1em; /* Tamaño del ícono */
            margin-right: 2px; /* Asegura un pequeño espacio entre el ícono y el texto */
        }

        /* Fondo de eliminación (Basurero) */
        .delete-background {
            width: 100px; /* Ancho fijo */
            background-color: #f8d7da; /* Fondo rojo claro */
            cursor: pointer; /* Indica que es interactivo */
            height: 100%;
        }

        /* Fondo de edición (Lápiz) */
        .edit-background {
            width: 100px; /* Ancho fijo */
            background-color: #d1e7dd; /* Fondo verde claro */
            cursor: pointer; /* Indica que es interactivo */
            height: 100%;
        }

        /* Contenido principal que se desliza */
        .item-content {
            transition: transform 0.3s ease; /* Transición suave */
            max-height: 630px; /* Ajusta la altura según los 7 ítems */
            overflow-y: auto; /* Habilita el scroll vertical */
        }

        /* Al deslizar hacia la izquierda, revelar el fondo de edición */
        .usuario-item-wrapper.swipe-left .item-content {
            transform: translateX(-100px); /* Desplaza el contenido hacia la izquierda */
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
        /* Al deslizar hacia la derecha, revelar el fondo de eliminación */
        .usuario-item-wrapper.swipe-right .item-content {
            transform: translateX(100px); /* Desplaza el contenido hacia la derecha */
        }

        /* Asegurar que los fondos estén detrás del contenido */
        .delete-background, .edit-background {
            position: absolute;
            top: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Posicionar los fondos correctamente */
        .delete-background {
            left: 0;
        }

        .edit-background {
            right: 0;
        }
        
    </style>
</head>
<?php 
require_once '../../controllers/admin/TitularController.php';
$controller = new TitularController($pdo);

// Obtener usuarios para cada sección
$titulares = $controller->mostrarTitulares();
$usuarios = $controller->obtenerUsuariosPorRoles();
?>
<body class="bg-light" style="font-size: 0.75em;">
    <nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand btn" href="Ajustes.php">
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

<div class="container" style="margin-top: 70px; margin-bottom: 70px;">
    <h1 class="text-center mb-4">Lista de Titulares</h1>

<!-- Select y Botón para filtrar y modificar titulares -->
<div class="row mb-4 align-items-center">
    <!-- Columna para el Select (70%) -->
    <div class="col-md-8">
        <select class="form-select choices-select" id="selectUsuario" aria-label="Seleccionar Vendedor">
            <option value="" disabled selected>Seleccionar Vendedor</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= htmlspecialchars($usuario['id']); ?>"><?= htmlspecialchars($usuario['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <!-- Columna para el Botón (25%) -->
    <div class="col-md-3">
        <button class="btn btn-outline-secondary w-100" id="btnModificar" disabled>Modificar</button>
    </div>
</div>

    <!-- Lista de titulares -->
    <div class="list-group" id="listaTitulares">
        <?php foreach ($titulares as $titular): ?>
            <div class="usuario-item-wrapper position-relative">
                <!-- Fondo de eliminación (Basurero) -->
                <div class="delete-background position-absolute top-0 bottom-0 start-0 d-flex align-items-center justify-content-center">
                    <i class="bi bi-trash-fill text-danger fs-3"></i>
                </div>

                <!-- Fondo de edición (Lápiz) -->
                <div class="edit-background position-absolute top-0 bottom-0 end-0 d-flex align-items-center justify-content-center">
                    <i class="bi bi-pencil-fill text-warning fs-3"></i>
                </div>

                <!-- Contenido principal del elemento -->
                <div class="list-group-item list-group-item-action d-flex align-items-center item-content" data-id="<?= htmlspecialchars($titular['id']); ?>">
                    <i class="bi bi-people display-6 me-3 text-primary"></i>
                    <div>
                        <h6 class="mb-1"><?= htmlspecialchars($titular['nombre']); ?></h6>
                        <p class="mb-0"><strong>Código:</strong> <?= htmlspecialchars($titular['codigo']); ?></p>
                        <p class="mb-0"><strong>Vendedor/Supervendedor:</strong> <?= htmlspecialchars($titular['usuario_nombre']); ?></p>
                    </div>
                    <!-- Botones de Editar y Eliminar para Tablet y PC -->
                    <div class="d-none d-md-flex gap-2 ms-auto">
                        <button 
                            class="btn btn-sm btn-outline-warning editar-btn d-flex align-items-center gap-1" 
                            data-id="<?= htmlspecialchars($titular['id']); ?>" 
                            title="Editar"
                            onclick="event.stopPropagation();">
                            <i class="bi bi-pencil"></i>Modificar
                        </button>
                        <button 
                            class="btn btn-sm btn-outline-danger eliminar-btn d-flex align-items-center gap-1" 
                            data-id="<?= htmlspecialchars($titular['id']); ?>" 
                            title="Eliminar"
                            onclick="event.stopPropagation();">
                            <i class="bi bi-trash"></i>Eliminar
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal para registrar titular -->
    <div class="modal fade" id="modalRegistroTitular" tabindex="-1" style="margin-top: 90px;" aria-labelledby="modalRegistroTitularLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" id="formRegistroTitular">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRegistroTitularLabel">Registrar Nuevo Titular</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Campo oculto para la acción -->
                        <input type="hidden" name="action" value="registrar">

                        <div class="mb-3">
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" style="font-size: 0.99em;" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Código" style="font-size: 0.99em;" required>
                        </div>
                        <div class="mb-3">
                            <select class="form-select choices-select" id="usuario_id" name="usuario_id" style="font-size: 0.99em;" required>
                                <option value="" disabled selected>Seleccionar Vendedor</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= htmlspecialchars($usuario['id']); ?>"><?= htmlspecialchars($usuario['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cancelar</button>
                        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-save2"></i> Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar titular -->
    <div class="modal fade" id="modalEditarTitular" tabindex="-1" style="margin-top: 90px;" aria-labelledby="modalEditarTitularLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditarTitular">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarTitularLabel">Modificar Titular</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="titularId" name="id">
                        <div class="mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCodigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="editCodigo" name="codigo" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUsuarioId" class="form-label">Vendedor Asociado</label>
                            <select class="form-select choices-select" id="editUsuarioId" name="usuario_id" required>
                                <option value="" disabled>Seleccionar Vendedor</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= htmlspecialchars($usuario['id']); ?>"><?= htmlspecialchars($usuario['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cancelar</button>
                        <button type="submit" class="btn btn-outline-warning"><i class="bi bi-floppy"></i> Modificar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para reasignar titulares -->
    <div class="modal fade" id="modalReasignarTitulares" tabindex="-1" aria-labelledby="modalReasignarTitularesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formReasignarTitulares">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalReasignarTitularesLabel">Reasignar Titulares</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="usuario_original_id" name="usuario_original_id">
                        <div class="mb-3">
                            <label for="usuario_nuevo_id" class="form-label">Seleccionar Nuevo Vendedor</label>
                            <select class="form-select choices-select" id="usuario_nuevo_id" name="usuario_nuevo_id" required>
                                <option value="" disabled selected>Seleccionar Nuevo Vendedor</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= htmlspecialchars($usuario['id']); ?>"><?= htmlspecialchars($usuario['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-outline-primary">Realizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Botón flotante con menú desplegable -->
    <div class="dropdown">
    <a style="bottom: 12px; position: fixed; bottom: 30px; left: 85%; transform: translateX(-50%); width: 65px; height: 65px; z-index: 9999;" class="btn btn-success rounded-circle floating-btn d-flex align-items-center justify-content-center" type="button" id="floatingDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i id="floatingButtonIcon" class="bi bi-plus text-white" style="font-size: 2.8rem;"></i>
    </a>
        <ul class="dropdown-menu p-3 border-0 shadow-lg" aria-labelledby="floatingDropdown">
            <li>
                <a class="dropdown-item d-flex align-items-center" href="admin_dashboard.php">
                    <span class="fw-semibold me-5">Principal</span>
                    <i class="bi bi-house" style="font-size: 1.2rem; color: gray;"></i>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalRegistroTitular">
                    <span class="fw-semibold me-5">Registrar</span>
                    <i class="bi bi-person-add" style="font-size: 1.2rem; color: cyan;"></i>
                </a>
            </li>
        </ul>
    </div>

<!-- Scripts -->
<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar Choices.js en los selects de usuarios y almacenar las instancias
    const choicesInstances = {}; // Objeto para almacenar las instancias

    const selects = document.querySelectorAll('.choices-select');
    selects.forEach(select => {
        const choices = new Choices(select, {
            searchEnabled: true,
            shouldSort: true,
            placeholder: true,
            placeholderValue: 'Seleccionar Vendedor',
            noResultsText: 'No se encontraron resultados',
            itemSelectText: '',
            allowHTML: true,
            removeItemButton: false,
        });
        choicesInstances[select.id] = choices; // Almacenar la instancia usando el ID del select
    });

    // Manejo del formulario de registro
    document.getElementById('formRegistroTitular').addEventListener('submit', function(e) {
        e.preventDefault();  // Evita el envío tradicional del formulario

        const formData = new FormData(this);
        formData.append('action', 'registrar');  // Añade la acción de registro

        fetch('../../controllers/admin/TitularController.php', {  // Ruta correcta al controlador
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Titular registrado con éxito');
                location.reload();  // Recarga la página para ver al nuevo titular
            } else {
                // Muestra el error detallado recibido del servidor
                alert('Error al registrar titular: ' + (data.error || 'Desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);  // Muestra errores si falló la solicitud
            alert('Hubo un problema con el registro');
        });
    });

    // Seleccionar elementos del DOM
    const selectUsuario = document.getElementById('selectUsuario');
    const btnModificar = document.getElementById('btnModificar');
    const listaTitulares = document.getElementById('listaTitulares');

    // Manejar el cambio en el select de usuarios
    selectUsuario.addEventListener('change', function() {
        const usuario_id = this.value;

        if (usuario_id) {
            // Habilitar el botón de modificar
            btnModificar.disabled = false;

            // Enviar solicitud AJAX para obtener titulares
            const formData = new FormData();
            formData.append('action', 'obtener_titulares_por_usuario');
            formData.append('usuario_id', usuario_id);

            fetch('../../controllers/admin/TitularController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Limpiar la lista actual de titulares
                    listaTitulares.innerHTML = '';

                    // Verificar si hay titulares
                    if (data.titulares.length > 0) {
                        data.titulares.forEach(titular => {
                            const divWrapper = document.createElement('div');
                            divWrapper.classList.add('usuario-item-wrapper', 'position-relative');

                            divWrapper.innerHTML = `
                                <div class="delete-background position-absolute top-0 bottom-0 start-0 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-trash-fill text-danger fs-3"></i>
                                </div>
                                <div class="edit-background position-absolute top-0 bottom-0 end-0 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-pencil-fill text-warning fs-3"></i>
                                </div>
                                <div class="list-group-item list-group-item-action d-flex align-items-center item-content" data-id="${titular.id}">
                                    <i class="bi bi-people display-6 me-3 text-primary"></i>
                                    <div>
                                        <h6 class="mb-1">${titular.nombre}</h6>
                                        <p class="mb-0"><strong>Código:</strong> ${titular.codigo}</p>
                                        <p class="mb-0"><strong>Usuario:</strong> ${titular.usuario_nombre}</p>
                                    </div>
                                    <div class="d-none d-md-flex gap-2 ms-auto">
                                        <button 
                                            class="btn btn-sm btn-outline-warning editar-btn d-flex align-items-center gap-1" 
                                            data-id="${titular.id}" 
                                            title="Editar"
                                            onclick="event.stopPropagation();">
                                            <i class="bi bi-pencil"></i>Modificar
                                        </button>
                                        <button 
                                            class="btn btn-sm btn-outline-danger eliminar-btn d-flex align-items-center gap-1" 
                                            data-id="${titular.id}" 
                                            title="Eliminar"
                                            onclick="event.stopPropagation();">
                                            <i class="bi bi-trash"></i>Eliminar
                                        </button>
                                    </div>
                                </div>
                            `;

                            listaTitulares.appendChild(divWrapper);
                        });

                        // Re-activar los listeners de los nuevos botones
                        attachEditDeleteListeners();
                    } else {
                        listaTitulares.innerHTML = '<p>No hay titulares asociados a este usuario.</p>';
                    }
                } else {
                    alert('Error al obtener titulares: ' + (data.error || 'Desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema al obtener los titulares.');
            });
        } else {
            btnModificar.disabled = true;
            // Opcional: limpiar la lista de titulares
            listaTitulares.innerHTML = '';
        }
    });

    // Manejar el clic en el botón de modificar
    btnModificar.addEventListener('click', function() {
        const usuario_id = selectUsuario.value;
        if (usuario_id) {
            // Establecer el ID del usuario original en el formulario del modal
            document.getElementById('usuario_original_id').value = usuario_id;

            // Opcional: Deshabilitar el usuario actual en el select del modal para evitar reasignación a sí mismo
            const usuario_nuevo_select = document.getElementById('usuario_nuevo_id');
            const choicesNuevo = choicesInstances['usuario_nuevo_id'];

            // Resetear el select de nuevo usuario
            choicesNuevo.clearStore();
            choicesNuevo.setChoices([
                <?php foreach ($usuarios as $usuario): ?>
                    {
                        value: "<?= htmlspecialchars($usuario['id']); ?>",
                        label: "<?= htmlspecialchars($usuario['nombre']); ?>",
                        selected: false,
                        disabled: <?= $usuario['id'] == '<?= $usuario_id ?>' ? 'true' : 'false'; ?>
                    },
                <?php endforeach; ?>
            ], 'value', 'label', false);

            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('modalReasignarTitulares'));
            modal.show();
        }
    });

    // Manejar el envío del formulario de reasignación
    document.getElementById('formReasignarTitulares').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'reasignar_titulares');

        fetch('../../controllers/admin/TitularController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Titulares reasignados con éxito.');
                // Cerrar el modal
                const modalEl = document.getElementById('modalReasignarTitulares');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                // Refrescar la lista de titulares
                selectUsuario.dispatchEvent(new Event('change'));
            } else {
                alert('Error al reasignar titulares: ' + (data.error || 'Desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema al reasignar los titulares.');
        });
    });

    // Manejo del clic en los botones de editar
    function abrirModalEdicion(titularId) {
        fetch(`../../controllers/admin/TitularController.php?id=${titularId}`)
            .then(response => response.json())
            .then(titular => {
                if (titular && titular.id) {
                    document.getElementById('titularId').value = titular.id;
                    document.getElementById('editNombre').value = titular.nombre;
                    document.getElementById('editCodigo').value = titular.codigo;
                    
                    // Actualizar el select de usuario asociado
                    const editUsuarioSelect = document.getElementById('editUsuarioId');
                    editUsuarioSelect.value = titular.usuario_id;
                    
                    // Actualizar Choices.js para reflejar el cambio
                    if (choicesInstances['editUsuarioId']) {
                        choicesInstances['editUsuarioId'].setChoiceByValue(titular.usuario_id);
                    }

                    // Mostrar el modal de edición
                    const modal = new bootstrap.Modal(document.getElementById('modalEditarTitular'));
                    modal.show();
                } else {
                    alert('Titular no encontrado.');
                }
            })
            .catch(error => {
                console.error('Error al obtener el titular:', error);
                alert('Hubo un problema al obtener los datos del titular.');
            });
    }

    // Función para eliminar titular
    function eliminarTitular(titularId, wrapper) {
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('id', titularId);

        fetch('../../controllers/admin/TitularController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Titular eliminado con éxito.');
                // Remover el elemento de la lista sin recargar la página
                wrapper.remove();
            } else {
                console.error("Error en la respuesta:", data);
                alert('Error al eliminar titular: ' + (data.error || 'Desconocido'));
            }
        })
        .catch(error => {
            console.error('Error en el fetch:', error);
            alert('Hubo un problema al eliminar el titular.');
        });
    }

    // Manejo del envío del formulario de edición
    document.getElementById('formEditarTitular').addEventListener('submit', function(e) {
        e.preventDefault(); // Evita el envío tradicional del formulario

        const formData = new FormData(this);
        formData.append('action', 'actualizar');

        // Imprimir los datos enviados para depuración
        console.log("Datos enviados para actualización:");
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        fetch('../../controllers/admin/TitularController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Titular actualizado con éxito');
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarTitular'));
                modal.hide();
                location.reload(); // Recarga la página para reflejar los cambios
            } else {
                console.error("Error en la respuesta:", data);
                alert('Error al actualizar titular: ' + (data.error || 'Desconocido'));
            }
        })
        .catch(error => {
            console.error('Error en el fetch:', error);
            alert('Hubo un problema con la actualización');
        });
    });

    // Función para adjuntar listeners a botones de editar y eliminar
    function attachEditDeleteListeners() {
        // Editar
        document.querySelectorAll('.editar-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const titularId = e.currentTarget.getAttribute('data-id');
                console.log('Editar titular ID:', titularId); // Depuración
                abrirModalEdicion(titularId);
            });
        });

        // Eliminar
        document.querySelectorAll('.eliminar-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const titularId = e.currentTarget.getAttribute('data-id');
                console.log('Eliminar titular ID:', titularId); // Depuración
                const wrapper = e.currentTarget.closest('.usuario-item-wrapper');
                const confirmar = confirm('¿Estás seguro de que deseas eliminar a este titular? Esta acción no se puede deshacer.');
                if (confirmar) {
                    eliminarTitular(titularId, wrapper);
                }
            });
        });
    }

    // Inicialmente, adjuntar listeners a los botones existentes
    attachEditDeleteListeners();

    // Manejar el deslizamiento en dispositivos táctiles (Opcional)
    document.querySelectorAll('.usuario-item-wrapper').forEach(wrapper => {
        let startX;
        let currentX;
        let isSwiping = false;
        let swipeDirection = null; // 'left' o 'right'

        // Manejar el inicio del toque
        wrapper.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isSwiping = true;
            swipeDirection = null;
        });

        // Manejar el movimiento del toque
        wrapper.addEventListener('touchmove', (e) => {
            if (!isSwiping) return;

            currentX = e.touches[0].clientX;
            const diffX = currentX - startX;

            // Detectar la dirección del deslizamiento
            if (diffX > 50 && swipeDirection !== 'right') {
                swipeDirection = 'right';
                wrapper.classList.remove('swipe-left');
                wrapper.classList.add('swipe-right');
            } else if (diffX < -50 && swipeDirection !== 'left') {
                swipeDirection = 'left';
                wrapper.classList.remove('swipe-right');
                wrapper.classList.add('swipe-left');
            }
        });

        // Manejar el fin del toque
        wrapper.addEventListener('touchend', (e) => {
            isSwiping = false;
            // Si el deslizamiento no fue suficiente, restablecer
            if (swipeDirection === 'left' || swipeDirection === 'right') {
                // Mantener la clase para que el ícono permanezca visible
            } else {
                wrapper.classList.remove('swipe-left', 'swipe-right');
            }
        });

        // Manejar el clic en el fondo de eliminación (Basurero)
        wrapper.querySelector('.delete-background').addEventListener('click', () => {
            const titularId = wrapper.querySelector('.item-content').getAttribute('data-id');
            const confirmar = confirm('¿Estás seguro de que deseas eliminar a este titular? Esta acción no se puede deshacer.');
            if (confirmar) {
                eliminarTitular(titularId, wrapper);
            }
        });

        // Manejar el clic en el fondo de edición (Lápiz)
        wrapper.querySelector('.edit-background').addEventListener('click', () => {
            const titularId = wrapper.querySelector('.item-content').getAttribute('data-id');
            abrirModalEdicion(titularId);
            // Opcional: cerrar el deslizamiento después de abrir el modal
            wrapper.classList.remove('swipe-left', 'swipe-right');
        });
    });
});
</script>
</body>
</html>