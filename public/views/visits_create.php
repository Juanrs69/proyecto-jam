<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear visita</title>
</head>
<body>
    <h2>Crear nueva visita</h2>
    <?php if (isset($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= $GLOBALS['basePath'] ?>/visits">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <label>Motivo:
            <input type="text" name="motivo" required>
        </label><br>
        <label>Fecha:
            <input type="datetime-local" name="fecha" required>
        </label><br>
        <label>Visitante:
            <select name="visitante" required>
                <option value="">Seleccione un visitante</option>
                <?php foreach ($visitantes as $v): ?>
                    <option value="<?= htmlspecialchars($v['id']) ?>">
                        <?= htmlspecialchars($v['nombre']) ?> (ID: <?= htmlspecialchars($v['id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <button type="submit">Guardar</button>
    </form>
    <p><a href="<?= $GLOBALS['basePath'] ?>/visits">Volver al listado</a></p>
</body>
</html>
