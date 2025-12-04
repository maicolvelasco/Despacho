# Despacho

Este proyecto es una aplicación web para la gestión de despachos y ventas, desarrollada en PHP. Permite administrar clientes, productos, despachos, ventas y usuarios, facilitando el control y seguimiento de las operaciones comerciales.

## Requisitos

- **Servidor local:** Funciona perfectamente en entornos como **XAMPP** o **Laragon**.
- **PHP:** Versión compatible con los servidores mencionados.
- **Base de datos:** MySQL (configuración en `config/config.php`).

## Instalación

1. Clona o descarga el proyecto en la carpeta `htdocs` (XAMPP) o `www` (Laragon).
2. Configura la base de datos en `config/config.php`.
3. Inicia el servidor local y accede a la aplicación desde tu navegador.

## Tipos de usuario y funcionalidades


El sistema cuenta con varios tipos de usuario, cada uno con actividades específicas:

### 1. **Administrador**
🛠️ Gestión de usuarios, clientes y ajustes generales
📊 Acceso a reportes y dashboards
✏️ Modificación y supervisión de registros
🔔 Gestión de notificaciones

### 2. **Despacho**
📦 Registro y modificación de despachos de productos
🔍 Consulta de detalles de despachos realizados
📥 Gestión de recepción y entrega de productos

### 3. **Supervendedor**
🕵️ Supervisión de ventas y registros de vendedores
✏️ Modificación y consulta de detalles de ventas y pallets
📈 Acceso a reportes específicos de ventas

### 4. **Vendedor**
📝 Registro de ventas y productos entregados
🔍 Consulta de detalles de ventas propias
🔔 Recepción de notificaciones sobre operaciones
👤 Gestión y edición de perfil personal

## Estructura principal

📁 **Estructura del Proyecto**

```
Despacho/
├── index.php                    # Página principal de la aplicación
├── manifest.json                # Configuración PWA
├── service-worker.js            # Service worker para modo offline
├── error_log                    # Log de errores
│
├── config/
│   ├── autoload.php             # Autoload de dependencias
│   └── config.php               # Configuración de la base de datos
│
├── controllers/                 # Controladores por módulo y usuario
│   ├── LoginController.php      # Autenticación de usuarios
│   ├── admin/                   # Controladores administrativos
│   ├── despacho/                # Controladores de despacho
│   ├── supervendedor/           # Controladores de supervendedores
│   └── vendedor/                # Controladores de vendedores
│
├── models/                      # Modelos de acceso a datos
│   ├── ModeloCliente.php
│   ├── ModeloDespacho.php
│   └── ...
│
├── views/                       # Vistas para cada tipo de usuario
│   ├── login.php
│   ├── logout.php
│   ├── admin/
│   ├── despacho/
│   ├── supervendedor/
│   └── vendedor/
│
├── libs/
│   └── fpdf/                    # Librería FPDF para PDFs
│
├── vendor/                      # Dependencias instaladas por Composer
│
└── src/                         # Recursos estáticos (imágenes, logos, etc.)
```

## Licencia

Este proyecto es de uso privado y no está destinado a distribución pública.

---

Para cualquier duda o soporte, contacta al desarrollador principal.
