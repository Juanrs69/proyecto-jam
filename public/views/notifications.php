<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Notificaciones</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= htmlspecialchars($GLOBALS['basePath'] ?? '') ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container py-4">
  <?php include __DIR__ . '/partials/ui/toasts.php'; ?>
  <?php
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $bp = $GLOBALS['basePath'] ?? '';
    $pageTitle = 'Notificaciones';
    $breadcrumbs = [ ['label' => $pageTitle] ];
    $showSearch = false;
    include __DIR__ . '/partials/ui/header.php';
  ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Notificaciones</h2>
    <form method="post" action="<?= htmlspecialchars($bp) ?>/notificaciones/leer-todas" onsubmit="return confirm('¿Marcar todas como leídas?');">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <button type="submit" class="btn btn-outline-primary"><i class="bi bi-check2-all me-1"></i> Marcar todas como leídas</button>
    </form>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white"><i class="bi bi-bell-fill me-1"></i> No leídas</div>
        <ul class="list-group list-group-flush">
          <?php if (!empty($unread)): foreach ($unread as $n): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="me-3">
                <div class="fw-semibold"><?= htmlspecialchars($n['title']) ?></div>
                <div class="text-muted small"><?= nl2br(htmlspecialchars($n['body'] ?? '')) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($n['created_at']) ?></div>
              </div>
              <form method="post" action="<?= htmlspecialchars($bp) ?>/notificaciones/<?= urlencode($n['id']) ?>/leer">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button class="btn btn-sm btn-outline-success"><i class="bi bi-check2"></i> Leída</button>
              </form>
            </li>
          <?php endforeach; else: ?>
            <li class="list-group-item text-muted">Sin notificaciones pendientes.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header"><i class="bi bi-inbox me-1"></i> Historial</div>
        <ul class="list-group list-group-flush">
          <?php if (!empty($read)): foreach ($read as $n): ?>
            <li class="list-group-item">
              <div class="fw-semibold"><?= htmlspecialchars($n['title']) ?></div>
              <div class="text-muted small"><?= nl2br(htmlspecialchars($n['body'] ?? '')) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($n['created_at']) ?> · Leída el <?= htmlspecialchars($n['read_at']) ?></div>
            </li>
          <?php endforeach; else: ?>
            <li class="list-group-item text-muted">Sin historial.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
<footer class="text-center text-muted small py-3 mt-4">&copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
