# Controladores principales

## AuthController
- Responsable de autenticación y sesión.
- Acciones: `showLogin`, `login`, `logout`, `showRegister`, `register`, `panel`, `panelEmpleado`, `panelRecepcionista`.
- Genera y valida CSRF en formularios de login/registro.
- Redirige al panel correspondiente según el rol en sesión.

## VisitController
- CRUD de visitas y acciones de flujo.
- Acciones clave:
  - `index`: listado con filtros y paginación.
  - `showCreateForm` y `store`: creación (CSRF + asignación de anfitrión). Notifica al anfitrión.
  - `show` (detalle), `showEditForm`/`update` (solo admin).
  - `authorize` (autorizar/rechazar) y `markExit` (registrar salida) para admin/empleado.
  - `export`: CSV (solo admin).
- Seguridad: valida CSRF en todas las mutaciones y restringe por rol.
- Idempotencia: asegura columnas requeridas y la tabla `notifications`.

## VisitorController
- CRUD de visitantes.
- Acciones: `index`, `showCreateForm`/`store`, `show` (detalle), `showEditForm`/`update` (solo admin), `delete` (solo admin).
- Seguridad: CSRF en POST y control de roles.

Notas
- Todos usan PDO con consultas preparadas.
- Las vistas se renderizan con `ob_start()` incluyendo `public/views/*.php`.
- Redirecciones con `basePath` para URLs consistentes y seguras.
