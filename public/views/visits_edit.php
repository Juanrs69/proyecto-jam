<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar visita</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow p-4 w-100" style="max-width: 500px;">
    <h2 class="mb-4 text-center text-primary">Editar visita</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($visita): ?>
    <form method="post" action="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>/edit">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" name="motivo" class="form-control" value="<?= htmlspecialchars($visita['motivo']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="datetime-local" name="fecha" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($visita['fecha'])) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Departamento</label>
            <input type="text" name="departamento" class="form-control" value="<?= htmlspecialchars($visita['departamento'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Visitante</label>
            <select name="visitante" class="form-select" required>
                <option value="">Seleccione un visitante</option>
                <?php foreach ($visitantes as $v): ?>
                    <option value="<?= htmlspecialchars($v['id']) ?>" <?= $visita['visitante_id'] == $v['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['nombre']) ?> (ID: <?= htmlspecialchars($v['id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Salida (opcional)</label>
            <input type="datetime-local" name="salida" class="form-control" value="<?= !empty($visita['salida']) ? date('Y-m-d\TH:i', strtotime($visita['salida'])) : '' ?>">
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>Guardar cambios</button>
    </form>
    <?php else: ?>
        <div class="alert alert-warning">Visita no encontrada.</div>
    <?php endif; ?>
    <p class="text-center mt-3">
        <a href="<?= $GLOBALS['basePath'] ?>/visits" class="text-decoration-none text-secondary">Volver al listado</a>
    </p>
  </div>
  <footer class="text-center text-muted small py-3 mt-4 w-100 position-absolute bottom-0">
    &copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.
  </footer>
</body>
</html>
