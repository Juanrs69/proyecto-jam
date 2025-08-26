<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de visitante</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow p-4 w-100" style="max-width: 500px;">
    <h2 class="mb-4 text-center text-primary"><i class="bi bi-person-vcard me-1"></i>Detalle de visitante</h2>
    <?php if ($visitante): ?>
        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>ID:</strong> <?= htmlspecialchars($visitante['id']) ?></li>
            <li class="list-group-item"><strong>Nombre:</strong> <?= htmlspecialchars($visitante['nombre']) ?></li>
            <li class="list-group-item"><strong>Documento:</strong> <?= htmlspecialchars($visitante['documento']) ?></li>
            <li class="list-group-item"><strong>Empresa:</strong> <?= htmlspecialchars($visitante['empresa']) ?></li>
        </ul>

        <?php
        $pdo = (isset($pdo) && $pdo instanceof PDO) ? $pdo : (function() {
            return require __DIR__ . '/../../src/Config/database.php';
        })();
        $stmt = null;
        $visitas = [];
        if (!empty($visitante['id'])) {
            $stmt = $pdo->prepare("SELECT id, fecha, motivo, departamento FROM visitas WHERE visitante_id = ? ORDER BY fecha DESC");
            $stmt->execute([$visitante['id']]);
            $visitas = $stmt->fetchAll();
        }
        ?>
        <h5 class="mt-3">Visitas de este visitante</h5>
        <?php if (!empty($visitas)): ?>
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle bg-white">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Motivo</th>
                    <th>Departamento</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($visitas as $v): ?>
                  <tr>
                    <td><?= htmlspecialchars($v['id']) ?></td>
                    <td><?= htmlspecialchars($v['fecha']) ?></td>
                    <td><?= htmlspecialchars($v['motivo']) ?></td>
                    <td><?= htmlspecialchars($v['departamento'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Sin visitas registradas.</div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning">Visitante no encontrado.</div>
    <?php endif; ?>
    <p class="text-center mt-3">
        <a href="<?= $GLOBALS['basePath'] ?>/visitantes" class="text-decoration-none text-secondary">Volver al listado</a>
    </p>
  </div>
<footer class="text-center text-muted small py-3 mt-4 position-absolute bottom-0 w-100">
  &copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.
</footer>
</body>
</html>
</html>
