<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes</title>
    <link rel="icon" type="image/png" href="./src/LOGO ESQUINA WEB ICONO.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
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
<?php require_once '../../controllers/admin/AjustesController.php' ?>
<body class="bg-light d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand btn" href="admin_dashboard.php">
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

    <div class="container" style="margin-top: 70px; margin-bottom: 60px;">
        <h1 class="text-center">Ajustes</h1>
        <div class="row mt-3">
            <div class="col-md-6 mb-2">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="text-center">Usuarios</h2>
                        <i class="bi bi-person" style="font-size: 3rem;"></i> <i class="bi bi-cart4" style="font-size: 3rem;"></i> <!-- Aumenta el tamaño del icono -->
                        <p class="card-text mt-3">Gestión de Usuarios y Vendedores</p>
                        <a href="Usuarios.php" class="btn btn-primary w-100">Ingresar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-2">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="text-center">Titulares</h2>
                        <i class="bi bi-people" style="font-size: 3rem;"></i>
                        <p class="card-text mt-3">Gestion de Titulares</p>
                        <a href="Titulares.php" class="btn btn-primary w-100">Ingresar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-2">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="text-center">Clientes</h2>
                        <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                        <p class="card-text mt-3">Gestion de Clientes</p>
                        <a href="Clientes.php" class="btn btn-primary w-100">Ingresar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-2">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h2 class="text-center">Pallets</h2>
                        <i class="bi bi-stack" style="font-size: 3rem;"></i>
                        <p class="card-text mt-3">Gestion de Pallets</p>
                        <a href="Pallets.php" class="btn btn-primary w-100">Ingresar</a>
                    </div>
                </div>
            </div>
<div class="col-12 col-md-12 col-lg-12 mt-1">
    <div class="d-flex justify-content-center">
        <form action="backup.php" method="post">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-database"></i> Realizar Backup
            </button>
        </form>
    </div>
</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>