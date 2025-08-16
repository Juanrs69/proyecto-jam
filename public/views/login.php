<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
</head>
<body>
    <h2>Iniciar sesión</h2>
    <?php if (isset($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= $GLOBALS['basePath'] ?>/login">
        <label>Correo:
            <input type="email" name="email" required>
        </label><br>
        <label>Contraseña:
            <input type="password" name="password" required>
        </label><br>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="<?= $GLOBALS['basePath'] ?>/register">¿No tienes cuenta? Regístrate aquí</a></p>
</body>
</html>
