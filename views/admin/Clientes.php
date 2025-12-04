<?php 
require_once '../../controllers/admin/ClienteController.php';
$controller = new ClienteController($pdo);

// Obtener la página actual
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10; // Número de clientes por página

$clientes = $controller->mostrarClientes($pagina, $limite);
$totalClientes = $controller->contarClientes();
$totalPaginas = ceil($totalClientes / $limite);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes</title>
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

        /* Contenedor de los ítems con scroll */
        .items-scrollable {
            max-height: 630px; /* Ajusta la altura según los 7 ítems */
            overflow-y: auto; /* Habilita el scroll vertical */
        }
    </style>
</head>
<?php 
require_once '../../controllers/admin/ClienteController.php';
$controller = new ClienteController($pdo);

$clientes = $controller->mostrarClientes();
$titulares = $controller->obtenerTitulares();
?>
<body class="bg-light" style="font-size: 0.75em;">
    <!-- Navbar -->
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

    <!-- Contenedor Principal -->
    <div class="container" style="margin-top: 70px; margin-bottom: 70px;">
        <h1 class="text-center mb-4">Lista de Clientes</h1>

        <div class="list-group items-scrollable mb-3">
            <?php foreach ($clientes as $cliente): ?>
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
                    <div class="list-group-item list-group-item-action d-flex align-items-center item-content" data-id="<?= htmlspecialchars($cliente['id']); ?>">
                        <i class="bi bi-people-fill display-6 me-3 text-primary"></i>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($cliente['nombre']); ?></h6>
                            <p class="mb-0"><strong>Código:</strong> <?= htmlspecialchars($cliente['codigo']); ?></p>
                            <p class="mb-0"><strong>Titular:</strong> <?= htmlspecialchars($cliente['titular_nombre']); ?></p>
                        </div>
                        <!-- Botones de Editar y Eliminar para Tablet y PC -->
                        <div class="d-none d-md-flex gap-2 ms-auto">
                            <button 
                                class="btn btn-sm btn-outline-warning editar-btn d-flex align-items-center gap-1" 
                                data-id="<?= htmlspecialchars($cliente['id']); ?>" 
                                title="Editar"
                                onclick="event.stopPropagation();">
                                <i class="bi bi-pencil"></i>Modificar
                            </button>
                            <button 
                                class="btn btn-sm btn-outline-danger eliminar-btn d-flex align-items-center gap-1" 
                                data-id="<?= htmlspecialchars($cliente['id']); ?>" 
                                title="Eliminar"
                                onclick="event.stopPropagation();">
                                <i class="bi bi-trash"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
            <!-- Paginación -->
    <nav aria-label="Page navigation mt-2">
        <ul class="pagination justify-content-center">
            <?php if ($pagina > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $pagina - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i === $pagina ? 'active' : ''; ?>">
                    <a class="page-link" href="?pagina=<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($pagina < $totalPaginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $pagina + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

        <!-- Modal para registrar Cliente -->
        <div class="modal fade" id="modalRegistroCliente" tabindex="-1" style="margin-top: 90px;" aria-labelledby="modalRegistroClienteLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="formRegistroCliente">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalRegistroClienteLabel">Registrar Nuevo Cliente</h5>
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
                                <select class="form-select choices-select" id="titular_id" name="titular_id" style="font-size: 0.99em;" required>
                                    <option value="" disabled selected>Seleccionar Titular</option>
                                    <?php foreach ($titulares as $titular): ?>
                                        <option value="<?= htmlspecialchars($titular['id']); ?>"><?= htmlspecialchars($titular['nombre']); ?></option>
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

        <!-- Modal para editar Cliente -->
        <div class="modal fade" id="modalEditarCliente" tabindex="-1" style="margin-top: 90px;" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="formEditarCliente">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditarClienteLabel">Modificar Cliente</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="clienteId" name="id">
                            <div class="mb-3">
                                <label for="editNombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="editNombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="editCodigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="editCodigo" name="codigo" required>
                            </div>
                            <div class="mb-3">
                                <label for="editTitularId" class="form-label">Titular Asociado</label>
                                <select class="form-select choices-select" id="editTitularId" name="titular_id" required>
                                    <option value="" disabled>Seleccionar Titular</option>
                                    <?php foreach ($titulares as $titular): ?>
                                        <option value="<?= htmlspecialchars($titular['id']); ?>"><?= htmlspecialchars($titular['nombre']); ?></option>
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
                <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalRegistroCliente">
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
        // Inicializar Choices.js en los selects de titulares y almacenar las instancias
        const choicesInstances = {}; // Objeto para almacenar las instancias

        const selects = document.querySelectorAll('.choices-select');
        selects.forEach(select => {
            const choices = new Choices(select, {
                searchEnabled: true,
                shouldSort: true,
                placeholder: true,
                placeholderValue: 'Seleccionar Titular',
                noResultsText: 'No se encontraron resultados',
                itemSelectText: '',
                allowHTML: true,
                removeItemButton: false,
            });
            choicesInstances[select.id] = choices; // Almacenar la instancia usando el ID del select
        });

        // Manejo del formulario de registro
        document.getElementById('formRegistroCliente').addEventListener('submit', function(e) {
            e.preventDefault();  // Evita el envío tradicional del formulario

            const formData = new FormData(this);
            formData.append('action', 'registrar');  // Añade la acción de registro

            fetch('../../controllers/admin/ClienteController.php', {  // Ruta correcta al controlador
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cliente registrado con éxito');
                    location.reload();  // Recarga la página para ver al nuevo cliente
                } else {
                    // Muestra el error detallado recibido del servidor
                    alert('Error al registrar cliente: ' + (data.error || 'Desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);  // Muestra errores si falló la solicitud
                alert('Hubo un problema con el registro');
            });
        });

        // Manejar el clic en los botones de editar
        document.querySelectorAll('.editar-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const clienteId = e.currentTarget.getAttribute('data-id');
                console.log('Editar cliente ID:', clienteId); // Depuración
                abrirModalEdicion(clienteId);
            });
        });

        // Manejar el clic en los botones de eliminar
        document.querySelectorAll('.eliminar-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const clienteId = e.currentTarget.getAttribute('data-id');
                console.log('Eliminar cliente ID:', clienteId); // Depuración
                const wrapper = e.currentTarget.closest('.usuario-item-wrapper');
                const confirmar = confirm('¿Estás seguro de que deseas eliminar a este cliente? Esta acción no se puede deshacer.');
                if (confirmar) {
                    eliminarCliente(clienteId, wrapper);
                }
            });
        });

        // Función para abrir el modal de edición con los datos del cliente
        function abrirModalEdicion(clienteId) {
            fetch(`../../controllers/admin/ClienteController.php?id=${clienteId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Error en la respuesta del servidor");
                    }
                    return response.json();
                })
                .then(cliente => {
                    if (cliente && cliente.id) {
                        document.getElementById('clienteId').value = cliente.id;
                        document.getElementById('editNombre').value = cliente.nombre;
                        document.getElementById('editCodigo').value = cliente.codigo;
                        
                        // Actualizar el select de titular asociado
                        const editTitularSelect = document.getElementById('editTitularId');
                        editTitularSelect.value = cliente.titular_id;
                        
                        // Actualizar Choices.js para reflejar el cambio
                        if (choicesInstances['editTitularId']) {
                            choicesInstances['editTitularId'].setChoiceByValue(cliente.titular_id);
                        }

                        // Mostrar el modal de edición
                        const modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
                        modal.show();
                    } else {
                        alert('Cliente no encontrado.');
                    }
                })
                .catch(error => {
                    console.error('Error al obtener el cliente:', error);
                    alert('Hubo un problema al obtener los datos del cliente.');
                });
        }

        // Función para eliminar cliente
        function eliminarCliente(clienteId, wrapper) {
            fetch(`../../controllers/admin/ClienteController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=eliminar&id=${clienteId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cliente eliminado con éxito.');
                    // Remover el elemento de la lista sin recargar la página
                    wrapper.remove();
                } else {
                    console.error("Error en la respuesta:", data);
                    alert('Error al eliminar cliente: ' + (data.error || 'Desconocido'));
                }
            })
            .catch(error => {
                console.error('Error en el fetch:', error);
                alert('Hubo un problema al eliminar el cliente.');
            });
        }

        // Manejo del envío del formulario de edición
        document.getElementById('formEditarCliente').addEventListener('submit', function(e) {
            e.preventDefault(); // Evita el envío tradicional del formulario

            const formData = new FormData(this);
            formData.append('action', 'actualizar');

            // Imprimir los datos enviados para depuración
            console.log("Datos enviados para actualización:");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            fetch('../../controllers/admin/ClienteController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Cliente actualizado con éxito');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
                    modal.hide();
                    location.reload(); // Recarga la página para reflejar los cambios
                } else {
                    console.error("Error en la respuesta:", data);
                    alert('Error al actualizar cliente: ' + (data.error || 'Desconocido'));
                }
            })
            .catch(error => {
                console.error('Error en el fetch:', error);
                alert('Hubo un problema con la actualización');
            });
        });

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
                const clienteId = wrapper.querySelector('.item-content').getAttribute('data-id');
                const confirmar = confirm('¿Estás seguro de que deseas eliminar a este cliente? Esta acción no se puede deshacer.');
                if (confirmar) {
                    eliminarCliente(clienteId, wrapper);
                }
            });

            // Manejar el clic en el fondo de edición (Lápiz)
            wrapper.querySelector('.edit-background').addEventListener('click', () => {
                const clienteId = wrapper.querySelector('.item-content').getAttribute('data-id');
                abrirModalEdicion(clienteId);
                // Opcional: cerrar el deslizamiento después de abrir el modal
                wrapper.classList.remove('swipe-left', 'swipe-right');
            });
        });
    });
    </script>
</body>
</html>