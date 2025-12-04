<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios</title>
    <link rel="icon" type="image/png" href="./src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
require_once '../../controllers/admin/UsuarioController.php';
$controller = new UsuarioController($pdo);

// Obtener usuarios para cada sección
$usuariosTodos = $controller->mostrarUsuarios('todos');
$usuariosVendedores = $controller->mostrarUsuarios('vendedores');
$usuariosFabrica = $controller->mostrarUsuarios('fabrica');
$roles = $controller->obtenerRoles();
$departamentos = $controller->obtenerDepartamentos();
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
    <h1 class="text-center mb-4">Lista de Usuarios</h1>
    
    <!-- Botones de filtro -->
    <div class="btn-group w-100 mb-2" role="group" aria-label="Filtro de Usuarios">
        <button type="button" class="btn btn-outline-primary btn-filter" onclick="mostrarSeccion('todos')">
            <i class="bi bi-people-fill me-2"></i> Todos
        </button>
        <button type="button" class="btn btn-outline-secondary btn-filter" onclick="mostrarSeccion('vendedores')">
            <i class="bi bi-briefcase-fill me-2"></i> Vendedores
        </button>
        <button type="button" class="btn btn-outline-info btn-filter" onclick="mostrarSeccion('fabrica')">
            <i class="bi bi-building me-2"></i> Fábrica
        </button>
    </div>

    <section id="todos" class="usuario-seccion items-scrollable">
    <?php if (!empty($usuariosTodos)): ?>
        <div class="list-group">
            <?php foreach ($usuariosTodos as $usuario): ?>
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
                    <div class="list-group-item list-group-item-action d-flex align-items-center item-content" data-id="<?= htmlspecialchars($usuario['id']); ?>">
                        <?php 
                            // Asignar icono según el rol
                            $icono = '';
                            if ($usuario['rol_nombre'] === 'Administrador') {
                                $icono = 'bi-shield-lock text-danger';
                            } elseif ($usuario['rol_nombre'] === 'Despacho') {
                                $icono = 'bi-truck text-warning';
                            } elseif ($usuario['rol_nombre'] === 'Vendedor') {
                                $icono = 'bi-briefcase text-secondary';
                            } else {
                                $icono = 'bi-person text-primary';
                            }
                        ?>
                        <i class="bi <?= $icono ?> display-6 me-3"></i>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($usuario['nombre']); ?></h6>
                            <p class="mb-0"><strong>Departamento:</strong> <?= htmlspecialchars($usuario['departamento_nombre']); ?></p>
                            <p class="mb-0"><strong>Dirección:</strong> <?= htmlspecialchars($usuario['direccion']); ?></p>
                        </div>
                        <div class="d-none d-md-flex gap-2 ms-auto">
                            <button 
                                class="btn btn-sm btn-outline-warning editar-btn d-flex align-items-center gap-1" 
                                data-id="<?= htmlspecialchars($usuario['id']); ?>" 
                                title="Editar"
                                onclick="event.stopPropagation();">
                                <i class="bi bi-pencil"></i>Modificar</i>
                            </button>
                            <button 
                                class="btn btn-sm btn-outline-danger eliminar-btn d-flex align-items-center gap-1" 
                                data-id="<?= htmlspecialchars($usuario['id']); ?>" 
                                title="Eliminar"
                                onclick="event.stopPropagation();">
                                <i class="bi bi-trash"></i>Eliminar</i>
                            </button>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No hay usuarios disponibles.</p>
    <?php endif; ?>
</section>



    <!-- Sección Vendedores -->
    <section id="vendedores" class="usuario-seccion items-scrollable">
        <?php if (!empty($usuariosVendedores)): ?>
            <div class="list-group">
                <?php foreach ($usuariosVendedores as $usuario): ?>
                    <div class="usuario-item-wrapper position-relative">
                        <!-- Fondo de eliminación (Basurero) -->
                        <div class="delete-background position-absolute top-0 bottom-0 start-0 d-flex align-items-center justify-content-center">
                            <i class="bi bi-trash-fill text-danger fs-3"></i>
                        </div>

                        <!-- Fondo de edición (Lápiz) -->
                        <div class="edit-background position-absolute top-0 bottom-0 end-0 d-flex align-items-center justify-content-center">
                            <i class="bi bi-pencil-fill text-warning fs-3"></i>
                        </div>
                        <div class="list-group-item list-group-item-action d-flex align-items-center item-content" data-id="<?= htmlspecialchars($usuario['id']); ?>">
                            <i class="bi bi-briefcase display-6 text-secondary me-3"></i>
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($usuario['nombre']); ?></h6>
                                <p class="mb-0"><strong>Departamento:</strong> <?= htmlspecialchars($usuario['departamento_nombre']); ?></p>
                                <p class="mb-0"><strong>Dirección:</strong> <?= htmlspecialchars($usuario['direccion']); ?></p>
                            </div>
                            <div class="d-none d-md-flex gap-2 ms-auto">
                                <button 
                                    class="btn btn-sm btn-outline-warning editar-btn d-flex align-items-center gap-1" 
                                    data-id="<?= htmlspecialchars($usuario['id']); ?>" 
                                    title="Editar"
                                    onclick="event.stopPropagation();">
                                    <i class="bi bi-pencil"></i>Modificar</i>
                                </button>
                                <button 
                                    class="btn btn-sm btn-outline-danger eliminar-btn d-flex align-items-center gap-1" 
                                    data-id="<?= htmlspecialchars($usuario['id']); ?>" 
                                    title="Eliminar"
                                    onclick="event.stopPropagation();">
                                    <i class="bi bi-trash"></i>Eliminar</i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No hay vendedores disponibles.</p>
        <?php endif; ?>
    </section>

    <section id="fabrica" class="usuario-seccion items-scrollable">
        <?php if (!empty($usuariosFabrica)): ?>
            <div class="list-group">
                <?php foreach ($usuariosFabrica as $usuario): ?>
                    <div class="usuario-item-wrapper position-relative">
                    <!-- Fondo de eliminación (Basurero) -->
                    <div class="delete-background position-absolute top-0 bottom-0 start-0 d-flex align-items-center justify-content-center">
                        <i class="bi bi-trash-fill text-danger fs-3"></i>
                    </div>

                    <!-- Fondo de edición (Lápiz) -->
                    <div class="edit-background position-absolute top-0 bottom-0 end-0 d-flex align-items-center justify-content-center">
                        <i class="bi bi-pencil-fill text-warning fs-3"></i>
                    </div>
                    <div class="list-group-item list-group-item-action d-flex align-items-center item-content" data-id="<?= htmlspecialchars($usuario['id']); ?>">
                        <?php 
                            // Asignar icono según el rol en la sección Fábrica
                            $icono = '';
                            $rol = trim($usuario['rol_nombre']); // Elimina posibles espacios
                            if ($rol === 'Administrador') {
                                $icono = 'bi-shield-lock text-danger';
                            } elseif ($rol === 'Despacho') {
                                $icono = 'bi-truck text-warning';
                            } elseif ($rol === 'Vendedor') {
                                $icono = 'bi-briefcase text-secondary';
                            } else {
                                $icono = 'bi-person text-primary';
                            }
                        ?>
                            <i class="bi <?= $icono ?> display-6 me-3"></i>
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($usuario['nombre']); ?></h6>
                                <p class="mb-0"><strong>Departamento:</strong> <?= htmlspecialchars($usuario['departamento_nombre']); ?></p>
                                <p class="mb-0"><strong>Dirección:</strong> <?= htmlspecialchars($usuario['direccion']); ?></p>
                            </div>
                            <div class="d-none d-md-flex gap-2 ms-auto">
                                <button 
                                    class="btn btn-sm btn-outline-warning editar-btn d-flex align-items-center gap-1" 
                                    data-id="<?= htmlspecialchars($usuario['id']); ?>" 
                                    title="Editar"
                                    onclick="event.stopPropagation();">
                                    <i class="bi bi-pencil"></i>Modificar</i>
                                </button>
                                <button 
                                    class="btn btn-sm btn-outline-danger eliminar-btn d-flex align-items-center gap-1" 
                                    data-id="<?= htmlspecialchars($usuario['id']); ?>" 
                                    title="Eliminar"
                                    onclick="event.stopPropagation();">
                                    <i class="bi bi-trash"></i>Eliminar</i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No hay usuarios de fábrica disponibles.</p>
        <?php endif; ?>
    </section>

    <!-- Modal para registrar usuario -->
    <div class="modal fade" id="modalRegistroUsuario" tabindex="-1" style="margin-top: 90px;" aria-labelledby="modalRegistroUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" id="formRegistroUsuario">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRegistroUsuarioLabel">Registrar Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" style="font-size: 0.99em;" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Direccion" style="font-size: 0.99em;" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Codigo" style="font-size: 0.99em;" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contrasena" style="font-size: 0.99em;" required>
                        </div>
                        <div class="mb-3">
                            <select class="form-select" id="rol_id" name="rol_id" style="font-size: 0.99em;" required>
                                <option value="" disabled selected>Seleccionar Rol</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= htmlspecialchars($rol['id']); ?>"><?= htmlspecialchars($rol['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <select class="form-select" id="departamento_id" name="departamento_id" style="font-size: 0.99em;" required>
                                <option value="" disabled selected>Seleccionar Departamento</option>
                                <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?= htmlspecialchars($departamento['id']); ?>"><?= htmlspecialchars($departamento['nombre']); ?></option>
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

    <!-- Modal para editar usuario -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditarUsuario">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="usuarioId" name="id">
                        <div class="mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDireccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="editDireccion" name="direccion" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCodigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="editCodigo" name="codigo" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="editPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRolId" class="form-label">Rol</label>
                            <select class="form-select" id="editRolId" name="rol_id" required>
                                <option value="" disabled>Seleccionar Rol</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= htmlspecialchars($rol['id']); ?>"><?= htmlspecialchars($rol['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editDepartamentoId" class="form-label">Departamento</label>
                            <select class="form-select" id="editDepartamentoId" name="departamento_id" required>
                                <option value="" disabled>Seleccionar Departamento</option>
                                <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?= htmlspecialchars($departamento['id']); ?>"><?= htmlspecialchars($departamento['nombre']); ?></option>
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
                <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalRegistroUsuario">
                    <span class="fw-semibold me-5">Registrar</span>
                    <i class="bi bi-person-plus" style="font-size: 1.2rem; color: cyan;"></i>
                </a>
            </li>
        </ul>
    </div>
    
<!-- Script de JavaScript para controlar las secciones -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const floatingDropdown = document.getElementById('floatingDropdown');
        const floatingButtonIcon = document.getElementById('floatingButtonIcon');

        // Evento cuando el dropdown se abre
        floatingDropdown.addEventListener('show.bs.dropdown', function () {
            floatingButtonIcon.classList.add('rotated');
        });

        // Evento cuando el dropdown se cierra
        floatingDropdown.addEventListener('hide.bs.dropdown', function () {
            floatingButtonIcon.classList.remove('rotated');
        });
    });
    function mostrarSeccion(seccion) {
        // Oculta todas las secciones
        document.querySelectorAll('.usuario-seccion').forEach(sec => sec.classList.add('d-none'));

        // Muestra la sección seleccionada
        document.getElementById(seccion).classList.remove('d-none');

        // Guarda el estado seleccionado en localStorage
        localStorage.setItem('seccionSeleccionada', seccion);

        // Actualiza los estilos de los botones
        document.querySelectorAll('.btn-filter').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.replace('btn-primary', 'btn-outline-primary');
            btn.classList.replace('btn-secondary', 'btn-outline-secondary');
            btn.classList.replace('btn-info', 'btn-outline-info');
        });

        // Aplica el estilo activo al botón correspondiente
        const btnActivo = document.querySelector(`button[onclick="mostrarSeccion('${seccion}')"]`);
        btnActivo.classList.add('active');
        btnActivo.classList.replace('btn-outline-primary', 'btn-primary');
        btnActivo.classList.replace('btn-outline-secondary', 'btn-secondary');
        btnActivo.classList.replace('btn-outline-info', 'btn-info');
    }

    document.getElementById('formRegistroUsuario').addEventListener('submit', function(e) {
        e.preventDefault();  // Evita el envío tradicional del formulario

        const formData = new FormData(this);
        formData.append('action', 'registrar');
        fetch('../../controllers/admin/UsuarioController.php', {  // Ruta correcta al controlador
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario registrado con éxito');
                location.reload();  // Recarga la página para ver al nuevo usuario
            } else {
                alert('Error al registrar usuario');
            }
        })
        .catch(error => {
            console.error('Error:', error);  // Muestra errores si falló la solicitud
            alert('Hubo un problema con el registro');
        });
    });

        document.querySelectorAll('.list-group-item').forEach(item => {
        let startX;

        item.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });

        item.addEventListener('touchmove', (e) => {
            const diffX = startX - e.touches[0].clientX;
            if (diffX > 50) { // Si se desliza más de 50px a la izquierda
                item.classList.add('swipe-left');
            }
        });

        item.addEventListener('touchend', (e) => {
            if (item.classList.contains('swipe-left')) {
                const usuarioId = item.getAttribute('data-id');
                abrirModalEdicion(usuarioId);
                item.classList.remove('swipe-left');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
    const modalEditarUsuario = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));

    document.querySelectorAll('.editar-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const usuarioId = e.currentTarget.getAttribute('data-id');
            abrirModalEdicion(usuarioId);
        });
    });

    // Manejar el clic en los botones de eliminar
    document.querySelectorAll('.eliminar-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const usuarioId = e.currentTarget.getAttribute('data-id');
            const wrapper = e.currentTarget.closest('.usuario-item-wrapper');
            const confirmar = confirm('¿Estás seguro de que deseas eliminar a este usuario? Esta acción no se puede deshacer.');
            if (confirmar) {
                eliminarUsuario(usuarioId, wrapper);
            }
        });
    })
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
            const usuarioId = wrapper.querySelector('.item-content').getAttribute('data-id');
            const confirmar = confirm('¿Estás seguro de que deseas eliminar a este usuario? Esta acción no se puede deshacer.');
            if (confirmar) {
                eliminarUsuario(usuarioId, wrapper);
            }
        });

        // Manejar el clic en el fondo de edición (Lápiz)
        wrapper.querySelector('.edit-background').addEventListener('click', () => {
            const usuarioId = wrapper.querySelector('.item-content').getAttribute('data-id');
            abrirModalEdicion(usuarioId);
            // Opcional: cerrar el deslizamiento después de abrir el modal
            wrapper.classList.remove('swipe-left', 'swipe-right');
        });
    });

    // Función para abrir el modal de edición con los datos del usuario
    function abrirModalEdicion(usuarioId) {
        fetch(`../../controllers/admin/UsuarioController.php?id=${usuarioId}`)
            .then(response => response.json())
            .then(usuario => {
                if (usuario && usuario.id) {
                    document.getElementById('usuarioId').value = usuario.id;
                    document.getElementById('editNombre').value = usuario.nombre;
                    document.getElementById('editDireccion').value = usuario.direccion;
                    document.getElementById('editCodigo').value = usuario.codigo;
                    document.getElementById('editPassword').value = usuario.password;
                    document.getElementById('editRolId').value = usuario.rol_id;
                    document.getElementById('editDepartamentoId').value = usuario.departamento_id;
                    modalEditarUsuario.show();
                } else {
                    alert('Usuario no encontrado.');
                }
            })
            .catch(error => {
                console.error('Error al obtener el usuario:', error);
                alert('Hubo un problema al obtener los datos del usuario.');
            });
    }

    // Función para eliminar un usuario
    function eliminarUsuario(usuarioId, wrapper) {
        fetch(`../../controllers/admin/UsuarioController.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=eliminar&id=${usuarioId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario eliminado con éxito.');
                // Remover el elemento de la lista sin recargar la página
                wrapper.remove();
            } else {
                console.error("Error en la respuesta:", data);
                alert('Error al eliminar usuario: ' + (data.error || 'Desconocido'));
            }
        })
        .catch(error => {
            console.error('Error en el fetch:', error);
            alert('Hubo un problema al eliminar el usuario.');
        });
    }

    // Manejo del envío del formulario de edición
    document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
        e.preventDefault(); // Evita el envío tradicional del formulario

        const formData = new FormData(this);
        formData.append('action', 'actualizar');

        // Imprimir los datos enviados para depuración
        console.log("Datos enviados para actualización:");
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        fetch('../../controllers/admin/UsuarioController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario actualizado con éxito');
                modalEditarUsuario.hide();
                location.reload(); // Recarga la página para reflejar los cambios
            } else {
                console.error("Error en la respuesta:", data);
                alert('Error al actualizar usuario: ' + (data.error || 'Desconocido'));
            }
        })
        .catch(error => {
            console.error('Error en el fetch:', error);
            alert('Hubo un problema con la actualización');
        });
    });
});
    // Carga la sección seleccionada al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        const seccionSeleccionada = localStorage.getItem('seccionSeleccionada') || 'todos';
        mostrarSeccion(seccionSeleccionada);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>