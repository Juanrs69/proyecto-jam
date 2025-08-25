<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visitas</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Listado de visitas</h2>
        <a href="<?= $GLOBALS['basePath'] ?>/visits/create" class="btn btn-success"><i class="bi bi-calendar-plus me-1"></i>Nueva visita</a>
    </div>

    <!-- Filtros -->
    <?php
      $f = $filters ?? ['desde'=>'','hasta'=>'','dep'=>''];
      $qBase = function(array $extra = []) use ($f) {
          return http_build_query(array_filter([
              'desde' => $f['desde'] ?? '',
              'hasta' => $f['hasta'] ?? '',
              'dep'   => $f['dep']   ?? '',
          ] + $extra, fn($v) => $v !== '' && $v !== null));
      };
    ?>
    <form method="get" action="<?= $GLOBALS['basePath'] ?>/visits" class="row g-2 mb-3">
        <div class="col-12 col-md-3">
            <label class="form-label mb-0">Desde</label>
            <input type="date" name="desde" value="<?= htmlspecialchars($f['desde'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label mb-0">Hasta</label>
            <input type="date" name="hasta" value="<?= htmlspecialchars($f['hasta'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label mb-0">Departamento</label>
            <input type="text" name="dep" value="<?= htmlspecialchars($f['dep'] ?? '') ?>" class="form-control" placeholder="Ej: Recepción">
        </div>
        <div class="col-12 col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
            <a class="btn btn-secondary" href="<?= $GLOBALS['basePath'] ?>/visits">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Documento</th>
                <th>Departamento</th>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Obtener los documentos de los visitantes relacionados
            $documentos = [];
            if (!empty($visitas)) {
                $ids = array_column($visitas, 'visitante_id');
                if ($ids) {
                    $pdo = $pdo ?? (function() {
                        return require __DIR__ . '/../../src/Config/database.php';
                    })();
                    $in = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("SELECT id, documento FROM visitantes WHERE id IN ($in)");
                    $stmt->execute($ids);
                    foreach ($stmt->fetchAll() as $row) {
                        $documentos[$row['id']] = $row['documento'];
                    }
                }
            }
            ?>
            <?php if (!empty($visitas)): ?>
                <?php foreach ($visitas as $visita): ?>
                    <tr>
                        <td><?= htmlspecialchars($visita['id']) ?></td>
                        <td><?= htmlspecialchars($documentos[$visita['visitante_id']] ?? '-') ?></td>
                        <td><?= htmlspecialchars($visita['departamento'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($visita['fecha']) ?></td>
                        <td><?= htmlspecialchars($visita['motivo']) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>/edit" class="btn btn-outline-warning">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>
                                <form action="<?= $GLOBALS['basePath'] ?>/visits/<?= urlencode($visita['id']) ?>/delete" method="post" onsubmit="return confirm('¿Seguro que deseas eliminar esta visita?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No hay visitas registradas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- Paginación -->
    <?php
      $total   = $total   ?? 0;
      $perPage = $perPage ?? 10;
      $page    = $page    ?? 1;
      $pages = (int)ceil($total / $perPage);
      if ($pages > 1):
          $bp = $GLOBALS['basePath'];
          $baseUrl = $bp . '/visits';
    ?>
    <nav aria-label="Paginación">
      <ul class="pagination">
        <?php
          $prev = max(1, $page - 1);
          $next = min($pages, $page + 1);
          $qsPrev = $qBase(['p' => $prev]);
          $qsNext = $qBase(['p' => $next]);
        ?>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $baseUrl . '?' . $qsPrev ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $pages; $i++): $qs = $qBase(['p'=>$i]); ?>
          <li class="page-item <?= $i === (int)$page ? 'active' : '' ?>">
            <a class="page-link" href="<?= $baseUrl . '?' . $qs ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $baseUrl . '?' . $qsNext ?>">Siguiente</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
</div>
<footer class="text-center text-muted small py-3 mt-4">
  &copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.
</footer>
</body>
</html>
