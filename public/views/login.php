<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - VisitaSegura</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
  // Rutas públicas a documentación
  $docsUser = ($bp === '') ? '/docs/manual-usuario.html' : $bp . '/docs/manual-usuario.html';
  $docsTech = ($bp === '') ? '/docs/manual-tecnico.html' : $bp . '/docs/manual-tecnico.html';
  $docsIndex = ($bp === '') ? '/docs/' : $bp . '/docs/';
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
  <!-- Botón fijo de ayuda -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1040;">
    <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalAyuda" aria-label="Abrir ayuda" title="Abrir ayuda">
      <i class="bi bi-question-circle" aria-hidden="true"></i> ¿Necesitas ayuda?
    </button>
  </div>

  <!-- Modal Ayuda -->
  <div class="modal fade" id="modalAyuda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">¿Necesitas ayuda?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-2">Encuentra guías y respuestas rápidas en la documentación:</p>
          <ul class="small text-muted mb-0">
            <li>Manual de Usuario: flujos de visitas y permisos.</li>
            <li>Manual Técnico: arquitectura, rutas y base de datos.</li>
          </ul>
        </div>
        <div class="modal-footer">
          <a class="btn btn-primary" target="_blank" rel="noopener" href="<?= htmlspecialchars($docsIndex) ?>"><i class="bi bi-collection me-1"></i> Abrir documentación</a>
          <a class="btn btn-outline-primary" target="_blank" rel="noopener" href="<?= htmlspecialchars($docsUser) ?>">Manual de Usuario</a>
          <a class="btn btn-outline-secondary" target="_blank" rel="noopener" href="<?= htmlspecialchars($docsTech) ?>">Manual Técnico</a>
        </div>
      </div>
    </div>
  </div>
  <footer class="text-center text-muted small py-3 mt-4 position-absolute bottom-0 w-100">
    &copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.
  </footer>
</body>
</html>
