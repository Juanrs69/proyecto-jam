# VisitaSegura — Gestión de visitantes (PHP + MySQL)

Sistema web para registrar, autorizar y controlar visitas en una organización, con roles, trazabilidad y reportes. Construido para XAMPP (Apache + PHP 8 + MySQL/MariaDB).

## Demo local rápida
- App: http://localhost/visita-segura/public/login
- Documentación: http://localhost/visita-segura/public/docs/
	- Manual de Usuario: `public/docs/manual-usuario.html`
	- Manual Técnico: `public/docs/manual-tecnico.html`
	- Conexiones y flujo (resumen): `public/docs/conexiones.html`
	- Presentación (slides): `public/docs/presentacion/`
	- Proyecto JAM (viewer): `public/docs/proyecto-jam/`

## Funcionalidades
- Registro de visitantes y visitas con búsqueda y filtros.
- Autorización/Rechazo por empleado; registro de salida.
- Paneles por rol (admin, empleado, recepcionista) sin recargas completas (modales + toasts).
- Exportación CSV y notificaciones al anfitrión.
- Endurecimiento de seguridad (CSRF en mutaciones y control de roles).

## Roles y permisos
- Administrador: gestiona usuarios, ve y edita todo, exporta.
- Empleado: autoriza/rechaza visitas, registra salida, ve sus visitas.
- Recepcionista: crea visitantes y visitas.

## Requisitos
- XAMPP para Windows (Apache + MySQL/MariaDB).
- PHP 8.x habilitado.

## Instalación y arranque
1) Copia el proyecto en `C:\xampp\htdocs\visita-segura`.
2) Crea la base de datos `visita_segura` en phpMyAdmin.
3) Configura credenciales en `src/Config/database.php` (host, usuario, contraseña).
4) Ejecuta la migración canónica en phpMyAdmin:
	 - `database/sql/2025-08-26_notifications_and_visitas_alter.sql`
	 - Crea la tabla `notifications` y añade columnas: `salida`, `departamento`, `estado`, `autorizado_por`, `anfitrion_id` a `visitas`.
5) Inicia Apache y MySQL desde XAMPP.
6) Abre `http://localhost/visita-segura/public/login`.
	 - Si no hay usuarios, registra uno en `http://localhost/visita-segura/public/register`.
	 - Ajusta el `rol` del usuario en la tabla `usuarios` si es necesario (`administrador` | `empleado` | `recepcionista`).

## Estructura del proyecto
```
public/
	index.php
	views/
		login.php, panel.php, panel_empleado.php, panel_recepcionista.php,
		visits*.php, visitantes*.php, parciales y modales
	docs/
		index.html, manual-usuario.html, manual-tecnico.html,
		presentacion/, conexiones.html, proyecto-jam/
src/
	Config/
		database.php, routes.php
	Controller/
		AuthController.php, VisitController.php, VisitorController.php
database/
	sql/2025-08-26_notifications_and_visitas_alter.sql
```

## Rutas principales
GET
- `/login`, `/logout`, `/register`
- `/panel`, `/panel/empleado`, `/panel/recepcionista`
- `/visits`, `/visits/create`, `/visits/{id}` (detalle), `/visits/export`
- `/visitantes`, `/visitantes/create`, `/visitantes/{id}`

POST
- `/login`, `/register`, `/visits`, `/visitantes`
- `/visits/{id}/edit`, `/visits/{id}/delete`, `/visits/{id}/authorize`, `/visits/{id}/exit`
- `/visitantes/{id}/edit`, `/visitantes/{id}/delete`

## Seguridad
- Token CSRF por sesión validado en todas las mutaciones (POST).
- Control de acceso por rol en controladores y vistas.
- Redirecciones referer-safe en acciones para mantener contexto del panel.

## Créditos / Equipo
- Juan Alejandro Ramírez
- María del Mar Ramírez
- Jeison David León

Uso académico — 2025, Corporación Uniremington, Sede Cali.

