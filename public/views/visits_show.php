<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de visita</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow p-4 w-100" style="max-width: 500px;">
    <h2 class="mb-4 text-center text-primary">Detalle de visita</h2>
    <?php if ($visita): ?>
        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>ID:</strong> <?= htmlspecialchars($visita['id']) ?></li>
            <li class="list-group-item"><strong>ID Visitante:</strong> <?= htmlspecialchars($visita['visitante_id']) ?></li>
            <li class="list-group-item"><strong>Fecha:</strong> <?= htmlspecialchars($visita['fecha']) ?></li>
            <li class="list-group-item"><strong>Motivo:</strong> <?= htmlspecialchars($visita['motivo']) ?></li>
        </ul>
    <?php else: ?>
        <div class="alert alert-warning">Visita no encontrada.</div>
    <?php endif; ?>
    <p class="text-center mt-3">
        <a href="<?= $GLOBALS['basePath'] ?>/visits" class="text-decoration-none text-secondary">Volver al listado</a>
    </p>
  </div>
</body>
</html>
