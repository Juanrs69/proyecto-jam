<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - VisitaSegura</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
      /* Estilos mínimos para que se vea aceptable sin Tailwind */
      body { font-family: Arial, Helvetica, sans-serif; background:#f3f4f6; padding:30px; }
      .card{ background:#fff; max-width:420px; margin:0 auto; padding:20px; border-radius:6px; box-shadow:0 4px 10px rgba(0,0,0,0.05);}
      label{ display:block; margin:10px 0 4px; font-weight:600; }
      input{ width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:4px; }
      button{ margin-top:12px; width:100%; padding:10px; background:#2563eb;color:#fff;border:none;border-radius:4px; cursor:pointer;}
      .error{ background:#fee2e2; color:#b91c1c; padding:8px; border-radius:4px; margin-bottom:8px;}
      .small{ font-size:13px; color:#6b7280; margin-top:8px; text-align:center;}
    </style>
</head>
<body>
  <div class="card" role="main" aria-labelledby="h1">
    <h2 id="h1">Iniciar sesión</h2>

    <?php if (!empty($error)): ?>
      <div class="error" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php
      // Base path seguro (evita '//login' si basePath está vacío)
      $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
      $action = ($bp === '') ? '/login' : $bp . '/login';

      // Mantener el email en caso de fallo (si el controlador lo pasa)
      $emailValue = isset($email) ? $email : (isset($_POST['email']) ? $_POST['email'] : '');
    ?>

    <form method="post" action="<?= htmlspecialchars($action) ?>" autocomplete="on" novalidate>
      <!-- CSRF: descomenta y genera token en controlador -->
      <!-- <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"> -->

      <label for="email">Correo</label>
      <input id="email" type="email" name="email" required value="<?= htmlspecialchars($emailValue) ?>" autofocus>

      <label for="password">Contraseña</label>
      <input id="password" type="password" name="password" required>

      <button type="submit">Entrar</button>
    </form>

    <p class="small">
      <a href="<?= htmlspecialchars(($bp === '')? '/register' : $bp . '/register') ?>">¿No tienes cuenta? Regístrate</a>
    </p>
  </div>
</body>
</html>
