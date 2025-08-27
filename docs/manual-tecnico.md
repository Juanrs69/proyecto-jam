# Manual Técnico

## 1. Arquitectura
- PHP 8+ con PDO (MySQL/MariaDB).
- Patrón simple MVC: Rutas -> Controladores -> Vistas.
- Frontend: Bootstrap 5, Bootstrap Icons, Tailwind vía CDN.
- Sesiones PHP para autenticación y toasts; CSRF en todas las mutaciones.

## 2. Estructura del Proyecto
- `public/` archivos públicos y vistas.
- `src/Config/` configuración (database.php, routes.php).
- `src/Controller/` controladores (AuthController, VisitController, VisitorController).

## 3. Rutas Clave (src/Config/routes.php)
- Auth: /login, /logout, /register.
- Paneles: /panel, /panel/empleado, /panel/recepcionista.
- Visitantes: /visitantes (GET/POST), /visitantes/create, /visitantes/{id}, /visitantes/{id}/edit (POST).
- Visitas: /visits (GET/POST), /visits/create, /visits/export, /visits/{id}, /visits/{id}/edit (GET/POST), /visits/{id}/authorize (GET/POST), /visits/{id}/exit (GET/POST).
- Notificaciones: /notificaciones (GET), /notificaciones/leer-todas (POST), /notificaciones/{id}/leer (POST).

## 4. Seguridad
- CSRF: token en sesión (`$_SESSION['csrf_token']`) y verificación `hash_equals`.
- Roles: `requireRoles` en controladores protege acceso.
- Mutaciones POST-only; redirección por Referer para mantener UX en panel.

## 5. Base de Datos
Tablas relevantes:
- `usuarios` (id, nombre, documento, correo, hash_contrasena, rol, created_at).
- `visitantes` (id, nombre, documento, empresa).
- `visitas` (id, visitante_id, fecha, motivo, departamento NULL, estado ENUM, salida DATETIME NULL, autorizado_por VARCHAR(64) NULL, anfitrion_id VARCHAR(64) NULL).
- `notifications` (id, user_id, title, body, created_at, read_at).

### 5.1. SQL (XAMPP MySQL/MariaDB) para columnas faltantes
Ver `docs/sql-migraciones-xampp.sql` (incluir).

## 6. Controladores
- `VisitController`:
  - `index` filtros y paginación.
  - `store` crea visita; si rol empleado, autollenar `anfitrion_id`.
  - `authorize`/`markExit` acciones con CSRF y roles.
  - `ensureVisitasColumns` crea columnas si faltan (idempotente).
  - `export` CSV (solo admin).
- `VisitorController`: CRUD visitantes.
- `AuthController`: login/logout, paneles por rol.

## 7. Vistas
- Paneles (`public/views/panel*.php`) con navegación interna usando `?section`.
- Modales para acciones (no navegan a páginas nuevas).
- Parciales reutilizables: header (breadcrumb + campana), toasts, modales.

## 8. Despliegue local (XAMPP)
1. Copiar el proyecto a `c:\xampp\htdocs\visita-segura`.
2. Configurar `src/Config/database.php` con credenciales MySQL.
3. Crear BD y tablas básicas.
4. Importar SQL de migraciones.
5. Abrir en navegador: `http://localhost/visita-segura/public/`.

## 9. Logs y Troubleshooting
- Habilitar `display_errors` en desarrollo.
- Revisar `$_SESSION['flashes']` para toasts.
- Validar sintaxis rápidamente con `php -l`.

## 10. Próximos pasos
- Tests unitarios para controladores.
- Paginación configurable en listados.
- Selector de anfitrión en recepcionista.
