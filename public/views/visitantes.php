<!-- Listado de visitantes con enlace para agregar y eliminar (con CSRF) -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visitantes</title>
</head>
<body>
    <h2>Listado de visitantes</h2>
    <p><a href="<?= $GLOBALS['basePath'] ?>/visitantes/create">Agregar visitante</a></p>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Documento</th>
                <th>Empresa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($visitantes)): ?>
                <?php foreach ($visitantes as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['id']) ?></td>
                        <td><?= htmlspecialchars($v['nombre']) ?></td>
                        <td><?= htmlspecialchars($v['documento']) ?></td>
                        <td><?= htmlspecialchars($v['empresa']) ?></td>
                        <td>
                            <a href="<?= $GLOBALS['basePath'] ?>/visitantes/<?= urlencode($v['id']) ?>">Ver</a> |
                            <a href="<?= $GLOBALS['basePath'] ?>/visitantes/<?= urlencode($v['id']) ?>/edit">Editar</a> |
                            <form action="<?= $GLOBALS['basePath'] ?>/visitantes/<?= urlencode($v['id']) ?>/delete" method="post" style="display:inline" onsubmit="return confirm('¿Seguro que deseas eliminar este visitante?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No hay visitantes registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
