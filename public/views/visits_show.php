<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de visita</title>
</head>
<body>
    <h2>Detalle de visita</h2>
    <?php if ($visita): ?>
        <ul>
            <li><strong>ID:</strong> <?= htmlspecialchars($visita['id']) ?></li>
            <li><strong>ID Visitante:</strong> <?= htmlspecialchars($visita['visitante_id']) ?></li>
            <li><strong>Fecha:</strong> <?= htmlspecialchars($visita['fecha']) ?></li>
            <li><strong>Motivo:</strong> <?= htmlspecialchars($visita['motivo']) ?></li>
        </ul>
    <?php else: ?>
        <p>Visita no encontrada.</p>
    <?php endif; ?>
    <p><a href="<?= $GLOBALS['basePath'] ?>/visits">Volver al listado</a></p>
</body>
</html>
