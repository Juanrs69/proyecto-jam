<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar visitante</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= htmlspecialchars($GLOBALS['basePath'] ?? '') ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container py-4">
  <?php include __DIR__ . '/partials/ui/toasts.php'; ?>
  <?php $pageTitle = 'Editar visitante'; $breadcrumbs = [['label'=>'Visitantes','href'=>($GLOBALS['basePath'] ?? '').'/visitantes'], ['label'=>$pageTitle]]; $showSearch=false; include __DIR__ . '/partials/ui/header.php'; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($visitante)): ?>
  <form method="post" action="<?= htmlspecialchars(($GLOBALS['basePath'] ?? '') . '/visitantes/' . urlencode((string)$visitante['id']) . '/edit') ?>" class="col-md-6 col-lg-5">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <div class="mb-3"><label class="form-label">Nombre</label><input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($visitante['nombre']) ?>"></div>
    <div class="mb-3"><label class="form-label">Documento</label><input type="text" name="documento" class="form-control" required value="<?= htmlspecialchars($visitante['documento']) ?>"></div>
    <div class="mb-3"><label class="form-label">Empresa</label><input type="text" name="empresa" class="form-control" value="<?= htmlspecialchars($visitante['empresa']) ?>"></div>
    <button type="submit" class="btn btn-primary">Guardar cambios</button>
    <a class="btn btn-secondary" href="<?= htmlspecialchars($GLOBALS['basePath'] ?? '') ?>/visitantes">Cancelar</a>
  </form>
  <?php else: ?>
    <div class="alert alert-warning">Visitante no encontrado.</div>
  <?php endif; ?>
</div>
<footer class="text-center text-muted small py-3 mt-4">&copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
