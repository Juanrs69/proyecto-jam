<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Visitantes</title>
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
  $sessUser = $_SESSION['user'] ?? null;
  $rolActual = is_array($sessUser) ? ($sessUser['rol'] ?? '') : '';
    $canCreate = in_array($rolActual, ['administrador','empleado','recepcionista'], true);
    $isAdmin = $rolActual === 'administrador';
    $pageTitle = 'Visitantes';
    $breadcrumbs = [ ['label' => $pageTitle] ];
    $showSearch = true;
    include __DIR__ . '/partials/ui/header.php';
  ?>

  <div class="d-flex gap-2 mb-3">
    <?php if ($canCreate): ?>
      <a href="<?= htmlspecialchars($bp) ?>/visitantes/create" class="btn btn-success"><i class="bi bi-person-plus me-1"></i>Nuevo visitante</a>
    <?php endif; ?>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover align-middle bg-white">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Documento</th>
          <th>Empresa</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($visitantes)): ?>
        <?php foreach ($visitantes as $v): ?>
          <tr>
            <td><?= htmlspecialchars($v['nombre']) ?></td>
            <td><?= htmlspecialchars($v['documento']) ?></td>
            <td><?= htmlspecialchars($v['empresa']) ?></td>
            <td>
              <div class="d-flex flex-wrap gap-1">
                <a href="<?= htmlspecialchars($bp) ?>/visitantes/<?= urlencode($v['id']) ?>" class="btn btn-sm btn-outline-info" target="_blank"><i class="bi bi-eye"></i> Ver</a>
                <?php if ($isAdmin): ?>
                  <a href="<?= htmlspecialchars($bp) ?>/visitantes/<?= urlencode($v['id']) ?>/edit" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i> Editar</a>
                  <form method="post" action="<?= htmlspecialchars($bp) ?>/visitantes/<?= urlencode($v['id']) ?>/delete" onsubmit="return confirm('¿Seguro que deseas eliminar este visitante?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Eliminar</button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" class="text-center">No hay visitantes registrados.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<footer class="text-center text-muted small py-3 mt-4">&copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
