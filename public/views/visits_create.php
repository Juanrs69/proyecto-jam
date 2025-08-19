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
        <label>Motivo:
            <input type="text" name="motivo" required>
        </label><br>
        <label>Fecha:
            <input type="datetime-local" name="fecha" required>
        </label><br>
        <label>ID Visitante:
            <input type="number" name="visitante" required>
        </label><br>
        <button type="submit">Guardar</button>
    </form>
    <p><a href="<?= $GLOBALS['basePath'] ?>/visits">Volver al listado</a></p>
</body>
</html>
git config --global user.name "Juanrs69"
git config --global user.email "juanalejandro004@gmail.com"


git add .
git commit -m "Mensaje corto pero claro"
git push
