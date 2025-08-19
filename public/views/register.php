<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de usuario - VisitaSegura</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f3f4f6;padding:24px}
    .card{max-width:480px;margin:24px auto;padding:20px;background:#fff;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
    label{display:block;margin-top:10px;font-weight:600}
    input{width:100%;padding:9px;border:1px solid #d1d5db;border-radius:6px;margin-top:6px}
    button{margin-top:14px;width:100%;padding:10px;border-radius:6px;border:0;background:#2563eb;color:#fff;cursor:pointer}
    .error{background:#fee2e2;color:#991b1b;padding:8px;border-radius:6px;margin-bottom:10px}
    .small{font-size:13px;color:#6b7280;text-align:center;margin-top:12px}
  </style>
</head>
<body>
  <div class="card" role="main" aria-labelledby="h1">
    <h2 id="h1">Registro de usuario</h2>

    <?php if (!empty($error)): ?>
      <div class="error" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php
      // basePath seguro
      $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
      $action = ($bp === '') ? '/register' : $bp . '/register';

      // Valores previos (pasados por el controlador en caso de error)
      $old = $GLOBALS['register_old'] ?? [];
      $nombreVal = $old['nombre'] ?? ($_POST['nombre'] ?? '');
      $emailVal  = $old['email'] ?? ($_POST['email'] ?? '');
    ?>

    <form method="post" action="<?= htmlspecialchars($action) ?>" novalidate autocomplete="on">
      <!-- CSRF token (controlador debe generar $_SESSION['csrf_token']) -->
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

      <label for="nombre">Nombre</label>
      <input id="nombre" name="nombre" type="text" required value="<?= htmlspecialchars($nombreVal) ?>">

      <label for="email">Correo</label>
      <input id="email" name="email" type="email" required value="<?= htmlspecialchars($emailVal) ?>">

      <label for="password">Contraseña</label>
      <input id="password" name="password" type="password" required>

      <button type="submit">Registrar</button>
    </form>

    <p class="small">
      <a href="<?= htmlspecialchars(($bp === '')? '/login' : $bp . '/login') ?>">¿Ya tienes cuenta? Inicia sesión</a>
    </p>
  </div>
</body>
</html>
