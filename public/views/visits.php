<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visitas</title>
</head>
<body>
    <h2>Listado de visitas</h2>
    <p><a href="<?= $GLOBALS['basePath'] ?>/visits/create">Crear nueva visita</a></p>
    <table border="1" cellpadding="5">
        <thead>
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
                            <a href="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>">Ver</a>
                            |
                            <a href="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>/edit">Editar</a>
                            |
                            <form action="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>/delete" method="post" style="display:inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta visita?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No hay visitas registradas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
