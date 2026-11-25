# Plantilla Web Editable para PYMEs (PHP 8 + MySQL + Tailwind CSS)

Plantilla web orientada a negocios locales y pymes que necesitan presencia rápida, catálogo de servicios/precios, botón directo a WhatsApp y un panel administrativo sencillo para actualizar contenido sin tocar código.

## Características
- **Sitio público (`public/index.php`):** Renderiza datos del negocio y servicios activos desde MySQL, con enlaces directos a WhatsApp por servicio o consulta general.
- **Panel de administración (`admin/`):** Inicio de sesión con `password_hash()` / `password_verify()`, protección de sesión, tokens CSRF y CRUD de información general y servicios.
- **Seguridad base:** Consultas preparadas con PDO (`ERRMODE_EXCEPTION` y `EMULATE_PREPARES = false`) y escape de salida HTML (`htmlspecialchars`).

## Stack tecnológico
- **Backend:** PHP 8.2+ (PDO)
- **Base de datos:** MySQL / MariaDB
- **Frontend:** HTML5, Tailwind CSS (CDN)
- **Entorno local:** XAMPP / Apache

## Cómo ejecutar en local
1. Clona el repositorio dentro de tu carpeta pública de servidor local (por ejemplo, `C:\xampp\htdocs\plantilla-pyme`).
2. Inicia **Apache** y **MySQL** en XAMPP.
3. Abre `http://localhost/phpmyadmin`, crea la base de datos `pyme_base` (`utf8mb4_unicode_ci`) e importa o ejecuta el script SQL de tablas y datos iniciales.
4. Revisa las credenciales de conexión en `config/db.php` (`127.0.0.1`, `pyme_base`, `root`, contraseña vacía por defecto en XAMPP).
5. Crea tu usuario administrador con `password_hash()` en la tabla `admin_users` (y elimina cualquier script temporal de creación tras usarlo).
6. Accede a:
   - Sitio público: `http://localhost/plantilla-pyme/public/`
   - Panel admin: `http://localhost/plantilla-pyme/admin/login.php`

## Próximas mejoras
- Carga de imágenes por servicio con validación MIME y tamaño.
- Configuración de variables de entorno/archivo local separado de credenciales.
- Exportación y clonado rápido para nuevos clientes.
