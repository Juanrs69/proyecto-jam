<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - VisitaSegura</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow p-4 w-100" style="max-width: 400px;">
    <h2 class="mb-4 text-center text-primary">Iniciar sesión</h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php
      $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
      $action = ($bp === '') ? '/login' : $bp . '/login';
      $emailValue = isset($email) ? $email : (isset($_POST['email']) ? $_POST['email'] : '');
    ?>
    <form method="post" action="<?= htmlspecialchars($action) ?>" autocomplete="on" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div class="mb-3">
        <label for="email" class="form-label">Correo</label>
        <input id="email" type="email" name="email" class="form-control" required value="<?= htmlspecialchars($emailValue) ?>" autofocus>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input id="password" type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
    <p class="text-center mt-3 small">
      <a href="<?= htmlspecialchars(($bp === '')? '/register' : $bp . '/register') ?>" class="text-decoration-none text-secondary">¿No tienes cuenta? Regístrate</a>
    </p>
  </div>
</body>
</html>
