<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de usuario - VisitaSegura</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow p-4 w-100" style="max-width: 400px;">
    <h2 class="mb-4 text-center text-primary">Registro de usuario</h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php
      $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
      $action = ($bp === '') ? '/register' : $bp . '/register';
      $old = $GLOBALS['register_old'] ?? [];
      $nombreVal = $old['nombre'] ?? ($_POST['nombre'] ?? '');
      $emailVal  = $old['email'] ?? ($_POST['email'] ?? '');
    ?>
    <form method="post" action="<?= htmlspecialchars($action) ?>" novalidate autocomplete="on">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input id="nombre" name="nombre" type="text" class="form-control" required value="<?= htmlspecialchars($nombreVal) ?>">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Correo</label>
        <input id="email" name="email" type="email" class="form-control" required value="<?= htmlspecialchars($emailVal) ?>">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input id="password" name="password" type="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Registrar</button>
    </form>
    <p class="text-center mt-3 small">
      <a href="<?= htmlspecialchars(($bp === '')? '/login' : $bp . '/login') ?>" class="text-decoration-none text-secondary">¿Ya tienes cuenta? Inicia sesión</a>
    </p>
  </div>
  <footer class="text-center text-muted small py-3 mt-4 position-absolute bottom-0 w-100">
    &copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.
  </footer>
</body>
</html>
