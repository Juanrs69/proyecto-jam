<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visitas</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Listado de visitas</h2>
        <a href="<?= $GLOBALS['basePath'] ?>/visits/create" class="btn btn-success">Crear nueva visita</a>
    </div>
    <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>ID Visitante</th>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($visitas)): ?>
                <?php foreach ($visitas as $visita): ?>
                    <tr>
                        <td><?= htmlspecialchars($visita['id']) ?></td>
                        <td><?= htmlspecialchars($visita['visitante_id']) ?></td>
                        <td><?= htmlspecialchars($visita['fecha']) ?></td>
                        <td><?= htmlspecialchars($visita['motivo']) ?></td>
                        <td>
                            <a href="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                            <a href="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>/edit" class="btn btn-sm btn-outline-warning">Editar</a>
                            <form action="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>/delete" method="post" style="display:inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta visita?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No hay visitas registradas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
