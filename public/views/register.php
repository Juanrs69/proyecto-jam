<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de usuario</title>
</head>
<body>
    <h2>Registro de usuario</h2>
    <?php if (isset($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= $GLOBALS['basePath'] ?>/register">
        <label>Nombre:
            <input type="text" name="nombre" required>
        </label><br>
        <label>Correo:
            <input type="email" name="email" required>
        </label><br>
        <label>Contraseña:
            <input type="password" name="password" required>
        </label><br>
        <button type="submit">Registrar</button>
    </form>
    <p><a href="<?= $GLOBALS['basePath'] ?>/login">¿Ya tienes cuenta? Inicia sesión</a></p>
</body>
</html>
