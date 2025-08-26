<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear visita</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow p-4 w-100" style="max-width: 500px;">
    <h2 class="mb-4 text-center text-primary">Crear nueva visita</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= $GLOBALS['basePath'] ?>/visits">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" name="motivo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="datetime-local" name="fecha" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Departamento</label>
            <input type="text" name="departamento" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Visitante existente</label>
            <select name="visitante" class="form-select">
                <option value="">-- Seleccione un visitante existente --</option>
                <?php foreach ($visitantes as $v): ?>
                    <option value="<?= htmlspecialchars($v['id']) ?>">
                        <?= htmlspecialchars($v['nombre']) ?> (ID: <?= htmlspecialchars($v['id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">O registre un nuevo visitante abajo</div>
        </div>
                <?php
                    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
                    $rolActual = $_SESSION['user']['rol'] ?? '';
                ?>
                <?php if ($rolActual !== 'empleado'): ?>
                <div class="mb-3">
                        <label class="form-label">Empleado anfitrión</label>
                        <select name="anfitrion_id" class="form-select">
                                <option value="">-- Sin asignar --</option>
                                <?php foreach (($empleados ?? []) as $e): ?>
                                        <option value="<?= htmlspecialchars($e['id']) ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                                <?php endforeach; ?>
                        </select>
                </div>
                <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label">Empleado anfitrión</label>
                        <input type="text" class="form-control" value="Asignado a mí" disabled>
                    </div>
                <?php endif; ?>
        <div class="mb-3 border rounded p-2 bg-light">
            <div class="mb-2 fw-bold text-secondary">Nuevo visitante (opcional)</div>
            <label class="form-label">Nombre</label>
            <input type="text" name="nuevo_nombre" class="form-control mb-2" placeholder="Nombre completo">
            <label class="form-label">Documento</label>
            <input type="text" name="nuevo_documento" class="form-control mb-2" placeholder="Documento">
            <label class="form-label">Empresa</label>
            <input type="text" name="nuevo_empresa" class="form-control" placeholder="Empresa">
        </div>
        <button type="submit" class="btn btn-primary w-100">Guardar</button>
    </form>
    <p class="text-center mt-3">
        <a href="<?= $GLOBALS['basePath'] ?>/visits" class="text-decoration-none text-secondary">Volver al listado</a>
    </p>
  </div>
</body>
</html>
