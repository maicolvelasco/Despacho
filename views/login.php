<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="icon" type="image/png" href="./src/LOGO ESQUINA WEB ICONO.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Enlace al manifest -->
    <link rel="manifest" href="/manifest.json">
  
    <!-- Definir el color del tema -->
    <meta name="theme-color" content="#0000ff">
    <!-- Incluir Bulma CSS via CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
        <!-- Añade Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Iconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f5f5f5; /* Fondo claro */
            display: flex;
            align-items: flex-start;
            justify-content: center;
            min-height: 100vh;
            padding-top: 3rem; /* Espacio superior */
        }
        .card-custom {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 2rem;
            background-color: #ffffff;
            max-width: 400px;
            width: 100%;
        }
        .logo-img {
            max-height: 100px;
            margin: 0 auto 1rem auto;
            display: block;
        }
        /* Estilos para etiquetas flotantes */
        .floating-label {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .floating-label input {
            width: 100%;
            padding: 1rem 0.75rem 0.75rem 2.5rem;
            border: 1px solid #dbdbdb;
            border-radius: 4px;
            background: none;
            transition: border-color 0.3s;
        }
        .floating-label input:focus {
            border-color: #3273dc; /* Color azul de Bulma */
            outline: none;
        }
        .floating-label .icon {
            position: absolute;
            top: 50%;
            left: 0.75rem;
            transform: translateY(-50%);
            color: #7a7a7a;
            font-size: 1.1rem;
        }
        .floating-label label {
            position: absolute;
            top: 50%;
            left: 2.5rem;
            transform: translateY(-50%);
            background-color: #ffffff;
            padding: 0 0.25rem;
            color: #7a7a7a;
            transition: all 0.3s;
            pointer-events: none;
        }
        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label {
            top: -0.6rem;
            left: 2.5rem;
            font-size: 0.75rem;
            color: #3273dc;
        }
        .button-custom {
            background-color: #3273dc;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 0.75rem;
            width: 100%;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }
        .button-custom:hover {
            background-color: #2759a5;
        }
        .notification-custom {
            position: relative;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .notification-custom .delete {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
        }
        @media (max-width: 480px) {
            .card-custom {
                padding: 1.5rem;
            }
            .logo-img {
                max-height: 80px;
            }
        }
                .modal-custom {
            background: linear-gradient(135deg, #f6f8f9 0%, #e5ebee 100%);
        }
        .contact-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .contact-info i {
            margin-right: 10px;
            color: #007bff;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .profile-img {
            width: 80px;  /* Reducido de 100px a 80px */
            height: 80px; /* Reducido de 100px a 80px */
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
        }
        .contact-link {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .contact-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card-custom">
        <!-- Imagen del logo -->
        <img id="logoImage" src="/src/LOGO ESQUINA WEB.png" alt="Logo" class="mb-4 logo">
        <h1 class="title has-text-centered is-4">Iniciar Sesion</h1>
        
        <!-- Mensaje de error si existe -->
        <?php 
        session_start();
        if (isset($_SESSION['error'])): ?>
            <div class="notification is-danger notification-custom">
                <button class="delete"></button>
                <?= htmlspecialchars($_SESSION['error']); ?>
            </div>
        <?php 
            unset($_SESSION['error']);
        endif; 
        ?>
        
        <!-- Formulario de login -->
        <form action="../controllers/LoginController.php" method="POST">
            <div class="floating-label">
                <span class="icon">
                <i class="bi bi-person-fill"></i>
                </span>
                <input type="text" id="codigo" name="codigo" required placeholder=" " autocomplete="off">
                <label for="codigo">Codigo</label>
            </div>
            
            <div class="floating-label">
                <span class="icon">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <input type="password" id="password" name="password" required placeholder=" " autocomplete="off">
                <label for="password">Contrasena</label>
            </div>
            
            <button type="submit" class="button-custom" style="font-size: 20px;">Ingresar <i class="bi bi-box-arrow-in-right"></i></button>
        </form>
        <button id="installBtn" style="display: none;" class="button-custom">Instalar App</button>
    </div>
    
    <!-- Modal Profesional -->
    <div class="modal fade" id="logoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-custom shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title w-100 text-center" style="color: #333;">
                        <strong>Control y Gestion de Pallets</strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <!-- Foto de perfil o logo de la empresa -->
                            <img src="/src/LOGO ESQUINA WEB ICONO.png" alt="Perfil" class="profile-img mb-3">
                        </div>
                        <div class="col-md-8 text-start">
                            <div class="contact-info">
                                <i class="fas fa-building"></i>
                                <strong>Empresa:</strong> Duralit
                            </div>
                            <div class="contact-info">
                                <i class="fas fa-user-tie"></i>
                                <strong>Ingeniero:</strong> Andres Condorety
                            </div>
                            <div class="contact-info">
                                <i class="fas fa-code"></i>
                                <strong>Desarrollador:</strong> Maicol Arratia Velasco
                            </div>
                            <div class="contact-info">
                                <i class="fas fa-phone"></i>
                                <strong>Numero:</strong> 
                                <a href="https://wa.me/+59160776373" target="_blank" class="contact-link ms-2">
                                    +591 60776373
                                </a>
                            </div>
                            <div class="contact-info">
                                <i class="fas fa-envelope"></i>
                                <strong>Correo:</strong> 
                                <a href="mailto:maicolarratia4@gmail.com" class="contact-link ms-2">
                                    maicolarratia4@gmail.com
                                </a>
                            </div>
                            <div class="contact-info">
                                <i class="fab fa-github"></i>
                                <strong>GitHub:</strong> 
                                <a href="https://github.com/Maik1704" target="_blank" class="contact-link ms-2">
                                    Maicol-Arratia
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <a href="https://docs.google.com/document/d/1fCE--ORv-4rmD7LPfqOQf0Tm5TMtKH4l/export?format=pdf" 
                       class="btn btn-custom" 
                       download="Hoja_de_Vida_Maicol.pdf">
                        <i class="fas fa-file-download me-2"></i>Descargar Hoja de Vida
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enlace a Bootstrap JS y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Registrar el Service Worker y Manejar la Instalación -->
<script>
        // Lógica para mostrar el modal al hacer clic 5 veces en la imagen
        const logoImage = document.getElementById('logoImage');
        let clickCount = 0;
        let lastClickTime = 0;

        logoImage.addEventListener('click', (event) => {
            const currentTime = new Date().getTime();
            
            // Resetear el conteo si han pasado más de 2 segundos desde el último clic
            if (currentTime - lastClickTime > 2000) {
                clickCount = 0;
            }

            clickCount++;
            lastClickTime = currentTime;

            if (clickCount === 5) {
                const logoModal = new bootstrap.Modal(document.getElementById('logoModal'));
                logoModal.show();
                clickCount = 0; // Resetear el conteo
            }
        });
        
    let deferredPrompt;
    const installBtn = document.getElementById('installBtn');

    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
          .then(registration => {
            console.log('Service Worker registrado con éxito:', registration);
          })
          .catch(error => {
            console.log('Error al registrar el Service Worker:', error);
          });
      });
    }

    window.addEventListener('beforeinstallprompt', (e) => {
      // Previene que el navegador muestre el prompt automáticamente
      e.preventDefault();
      // Guarda el evento para poderlo mostrar más tarde
      deferredPrompt = e;
      // Muestra el botón de instalación
      installBtn.style.display = 'block';
    });

    installBtn.addEventListener('click', () => {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(choiceResult => {
          if (choiceResult.outcome === 'accepted') {
            console.log('Usuario aceptó la instalación');
          } else {
            console.log('Usuario rechazó la instalación');
          }
          deferredPrompt = null;
          installBtn.style.display = 'none';
        });
      }
    });
</script>

    <!-- JavaScript para cerrar notificaciones -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });
        });
    </script>
</body>
</html>