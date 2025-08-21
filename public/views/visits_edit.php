<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar visita</title>
</head>
<body>
    <h2>Editar visita</h2>
    <?php if (isset($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($visita): ?>
    <form method="post" action="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>/edit">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <label>Motivo:
            <input type="text" name="motivo" value="<?= htmlspecialchars($visita['motivo']) ?>" required>
        </label><br>
        <label>Fecha:
            <input type="datetime-local" name="fecha" value="<?= date('Y-m-d\TH:i', strtotime($visita['fecha'])) ?>" required>
        </label><br>
        <label>Visitante:
            <select name="visitante" required>
                <option value="">Seleccione un visitante</option>
                <?php foreach ($visitantes as $v): ?>
                    <option value="<?= htmlspecialchars($v['id']) ?>" <?= $visita['visitante_id'] == $v['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['nombre']) ?> (ID: <?= htmlspecialchars($v['id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <button type="submit">Guardar cambios</button>
    </form>
    <?php else: ?>
        <p>Visita no encontrada.</p>
    <?php endif; ?>
    <p><a href="<?= $GLOBALS['basePath'] ?>/visits">Volver al listado</a></p>
</body>
</html>
