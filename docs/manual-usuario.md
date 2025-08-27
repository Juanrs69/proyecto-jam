# Manual de Usuario

## 1. Acceso y Roles
- URL de inicio de sesión: /login
- Roles:
  - Administrador: gestiona usuarios, visitantes y visitas.
  - Empleado: autoriza/rechaza visitas y marca salidas; puede crear visita/visitante desde su panel.
  - Recepcionista: crea visitantes y visitas; consulta listados.

## 2. Navegación por Panel
- Panel principal según rol (Admin/Empleado/Recepcionista) con menú lateral e íconos.
- Secciones comunes: Dashboard, Visitantes, Visitas, Perfil, Cambiar contraseña.
- La navegación de panel usa `?section=` para cambiar de vista sin salir del panel.

## 3. Notificaciones
- Campana en el encabezado.
- Las notificaciones se muestran en un modal (no abre página nueva).
- Botones para marcar como leídas y marcar todas.

## 4. Visitantes
- Listado con búsqueda y acciones.
- Crear visitante:
  - Desde Recepcionista o Empleado: botón "Nuevo visitante" (modal).
  - Completar Nombre y Documento (requeridos), Empresa (opcional).

## 5. Visitas
- Listado con filtros, exportación a CSV y acciones por rol.
- Crear visita:
  - Desde Recepcionista o Empleado: botón "Nueva visita" (modal).
  - Campos: Motivo, Fecha, Departamento, Visitante.
  - Para Empleado, el sistema asigna automáticamente como anfitrión.
- Autorizar/Rechazar visita (Empleado/Admin):
  - Botones en la tabla abren modal de confirmación.
- Marcar salida (Empleado/Admin):
  - Botón en la fila cuando la visita está autorizada y sin salida.

## 6. Perfil y Contraseña
- Perfil: datos básicos de la cuenta.
- Cambiar contraseña: requiere contraseña actual y nueva (mínimo 6 caracteres).

## 7. Mensajes y Errores
- Confirmaciones y errores aparecen como toasts o alertas dentro del panel.

## 8. Consejos
- Mantén tu sesión activa solo en dispositivos de confianza.
- Usa exportación CSV para reportes rápidos.
