<!-- Formulario para agregar visitante, con CSRF y mensajes de error -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar visitante</title>
</head>
<body>
    <h2>Agregar visitante</h2>
    <?php if (isset($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= $GLOBALS['basePath'] ?>/visitantes">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <label>Nombre:
            <input type="text" name="nombre" required>
        </label><br>
        <label>Documento:
            <input type="text" name="documento" required>
        </label><br>
        <label>Empresa:
            <input type="text" name="empresa">
        </label><br>
        <button type="submit">Guardar</button>
    </form>
    <p><a href="<?= $GLOBALS['basePath'] ?>/visitantes">Volver al listado</a></p>
</body>
</html>
