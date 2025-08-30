<?php // Inicio del script del panel de empleado
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$user = $_SESSION['user'] ?? null;
$rolSafe = is_array($user) ? ($user['rol'] ?? '') : '';
if (!is_array($user) || $rolSafe !== 'empleado') {
    header('Location: ' . ($GLOBALS['basePath'] ?? '') . '/login'); exit;
}
// Evitar bucle de inclusión con panel.php
$fromRolePanel = true;
$pdo = isset($pdo) && $pdo instanceof PDO ? $pdo : (require __DIR__ . '/../../src/Config/database.php');
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
$bp = $GLOBALS['basePath'] ?? '';
if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
$section = $_GET['section'] ?? 'dashboard';
$uid = is_array($user) ? ($user['id'] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><title>Panel empleado</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= h($bp) ?>/assets/css/app.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>.sidebar .nav-link{color:#e5e7eb}</style> <!-- Color del texto del sidebar -->
</head>
<body class="bg-gray-100">
<div class="container-fluid">
  <div class="row">
  <nav class="col-md-3 col-lg-2 d-md-block py-4 px-2 sidebar"> <!-- Sidebar con navegación por secciones -->
      <div class="text-center mb-4">
        <div class="fw-semibold">Empleado</div>
        <div class="small text-white-50"><?= h($user['nombre'] ?? '') ?></div>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item mb-2"><a class="nav-link<?= $section==='dashboard'?' active':'' ?>" href="<?= h($bp) ?>/panel/empleado?section=dashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link<?= $section==='visitas'?' active':'' ?>" href="<?= h($bp) ?>/panel/empleado?section=visitas"><i class="bi bi-people me-1"></i>Visitas</a></li>
        <li class="nav-item mb-2"><a class="nav-link<?= $section==='perfil'?' active':'' ?>" href="<?= h($bp) ?>/panel/empleado?section=perfil"><i class="bi bi-person-circle me-1"></i>Perfil</a></li>
        <li class="nav-item mb-2"><a class="nav-link<?= $section==='cambiar'?' active':'' ?>" href="<?= h($bp) ?>/panel/empleado?section=cambiar"><i class="bi bi-key me-1"></i>Cambiar contraseña</a></li>
        <li class="nav-item mt-4"><a class="nav-link text-danger" href="<?= h($bp) ?>/logout"><i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</a></li>
      </ul>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
  <?php include __DIR__ . '/partials/ui/toasts.php'; ?> <!-- Toastr de feedback -->
      <?php
        // Preparar header
        $titles = [
          'dashboard' => 'Dashboard',
          'visitas'   => 'Mis visitas',
          'perfil'    => 'Mi perfil',
          'cambiar'   => 'Cambiar contraseña',
        ];
        $pageTitle = $titles[$section] ?? 'Panel';
        $breadcrumbs = [ ['label' => $pageTitle] ];
  $showSearch = ($section === 'visitas'); // Mostrar buscador solo en visitas
        include __DIR__ . '/partials/ui/header.php';
      ?>

      <?php switch ($section): case 'dashboard': ?>
        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5 class="card-title">Bienvenido<?= $user && !empty($user['nombre']) ? ', '.h($user['nombre']) : '' ?></h5>
                <p class="card-text text-muted">Desde aquí podrás autorizar/rechazar visitas y marcar salidas.</p>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
              <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Accesos rápidos</h6>
                <a class="btn btn-primary me-2" href="<?= h($bp) ?>/panel/empleado?section=visitas"><i class="bi bi-list-ul me-1"></i>Ver visitas</a>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalCrearVisita"><i class="bi bi-calendar-plus me-1"></i>Nueva visita</button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearVisitante"><i class="bi bi-person-plus me-1"></i>Nuevo visitante</button>
              </div>
            </div>
          </div>
        </div>

  <?php break; case 'visitas': ?> <!-- Sección de listado de visitas -->
        <?php
          $visitas = []; // Arreglo de resultados a renderizar
          $viewAll = (isset($_GET['all']) && $_GET['all'] === '1');
          try {
            if ($viewAll) {
              // Ver todas las visitas recientes
              $sql2 = "SELECT v.id, v.motivo, v.fecha, v.departamento, v.estado, v.salida, vi.nombre AS visitante_nombre
                       FROM visitas v
                       LEFT JOIN visitantes vi ON vi.id = v.visitante_id
                       ORDER BY v.fecha DESC LIMIT 100";
              $visitas = $pdo->query($sql2)->fetchAll();
            } else if ($uid) {
              // Solo mis visitas (anfitrión o autorizadas por mí)
              $sql = "SELECT v.id, v.motivo, v.fecha, v.departamento, v.estado, v.salida, vi.nombre AS visitante_nombre
                      FROM visitas v
                      LEFT JOIN visitantes vi ON vi.id = v.visitante_id
                      WHERE (v.anfitrion_id = :uid OR v.autorizado_por = :uid)
                      ORDER BY v.fecha DESC LIMIT 100";
              $st = $pdo->prepare($sql);
              $st->execute([':uid' => $uid]);
              $visitas = $st->fetchAll();
            }
            // Fallback a todas si no hay uid o no hubo filas y no se pidió ver todas
            if ((!$uid || !$visitas) && !$viewAll) {
              $sql2 = "SELECT v.id, v.motivo, v.fecha, v.departamento, v.estado, v.salida, vi.nombre AS visitante_nombre
                       FROM visitas v
                       LEFT JOIN visitantes vi ON vi.id = v.visitante_id
                       ORDER BY v.fecha DESC LIMIT 100";
              $visitas = $pdo->query($sql2)->fetchAll();
            }
          } catch (\Throwable $e) {
            // Fallback si faltan columnas o cualquier otra excepción
            try {
              $sql2 = "SELECT v.id, v.motivo, v.fecha, v.departamento, v.estado, v.salida, vi.nombre AS visitante_nombre
                       FROM visitas v
                       LEFT JOIN visitantes vi ON vi.id = v.visitante_id
                       ORDER BY v.fecha DESC LIMIT 100";
              $visitas = $pdo->query($sql2)->fetchAll();
            } catch (\Throwable $e2) {
              $visitas = [];
            }
          }
        ?>
  <div class="d-flex flex-wrap gap-2 align-items-center mb-3"> <!-- Barra de acciones de visitas -->
          <h5 class="m-0">Mis visitas</h5>
          <div class="ms-auto d-flex flex-wrap gap-2">
            <a href="<?= h($bp) ?>/visits/export" class="btn btn-outline-secondary"><i class="bi bi-filetype-csv me-1"></i>Exportar CSV</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearVisita"><i class="bi bi-calendar-plus me-1"></i>Nueva visita</button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearVisitante"><i class="bi bi-person-plus me-1"></i>Nuevo visitante</button>
            <?php if ($viewAll): ?>
              <a class="btn btn-outline-primary" href="<?= h($bp) ?>/panel/empleado?section=visitas"><i class="bi bi-funnel me-1"></i>Solo mis visitas</a>
            <?php else: ?>
              <a class="btn btn-outline-primary" href="<?= h($bp) ?>/panel/empleado?section=visitas&all=1"><i class="bi bi-list-ul me-1"></i>Ver todas</a>
            <?php endif; ?>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover align-middle bg-white">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Visitante</th>
                <th>Motivo</th>
                <th>Fecha</th>
                <th>Departamento</th>
                <th>Estado</th>
                <th>Salida</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($visitas): foreach ($visitas as $v): ?>
                <tr>
                  <td><?= h($v['id']) ?></td>
                  <td><?= h($v['visitante_nombre'] ?? '-') ?></td>
                  <td><?= h($v['motivo'] ?? '-') ?></td>
                  <td><?= h($v['fecha'] ?? '-') ?></td>
                  <td><?= h($v['departamento'] ?? '-') ?></td>
                  <td>
                    <?php $estado = $v['estado'] ?? 'pendiente';
                      $badge = $estado==='autorizada'?'success':($estado==='rechazada'?'danger':'secondary'); ?>
                    <span class="badge text-bg-<?= $badge ?>"><?= h(ucfirst($estado)) ?></span>
                  </td>
                  <td><?= h($v['salida'] ?? '-') ?></td>
                  <td class="text-nowrap">
                    <button class="btn btn-sm btn-outline-primary" title="Ver" data-bs-toggle="modal" data-bs-target="#modalVerVisita" data-id="<?= h($v['id']) ?>">
                      <i class="bi bi-eye"></i>
                    </button>
                    <?php if (($v['estado'] ?? '') === 'pendiente'): ?>
                      <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalAutorizarVisita" data-id="<?= h($v['id']) ?>" data-decision="autorizar"><i class="bi bi-check2"></i></button>
                      <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalAutorizarVisita" data-id="<?= h($v['id']) ?>" data-decision="rechazar"><i class="bi bi-x"></i></button>
                    <?php endif; ?>
                    <?php if (($v['estado'] ?? '') === 'autorizada' && empty($v['salida'])): ?>
                      <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalConfirmSalida" data-id="<?= h($v['id']) ?>"><i class="bi bi-door-open"></i></button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="8" class="text-center">Sin visitas.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
  <?php include __DIR__ . '/partials/modals_visita_actions.php'; ?> <!-- Modales de autorizar/rechazar/salida -->

        <?php
          // Preparar datos para modales de creación
          try { $visitantesC = $pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre")->fetchAll(); } catch (\Throwable $e) { $visitantesC = []; }
        ?>
  <!-- Modal: Crear visitante (POST /visitantes) -->
        <div class="modal fade" id="modalCrearVisitante" tabindex="-1" aria-hidden="true" aria-labelledby="modalCrearVisitanteLabel">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post" action="<?= h($bp) ?>/visitantes">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalCrearVisitanteLabel">Nuevo visitante</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                  <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Documento</label>
                    <input type="text" name="documento" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Empresa</label>
                    <input type="text" name="empresa" class="form-control">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
              </form>
            </div>
          </div>
        </div>

  <!-- Modal: Crear visita (POST /visits) -->
        <div class="modal fade" id="modalCrearVisita" tabindex="-1" aria-hidden="true" aria-labelledby="modalCrearVisitaLabel">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post" action="<?= h($bp) ?>/visits">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalCrearVisitaLabel">Nueva visita</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                  <div class="mb-3">
                    <label class="form-label">Motivo</label>
                    <input type="text" name="motivo" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Fecha</label>
                    <input type="datetime-local" name="fecha" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Departamento</label>
                    <input type="text" name="departamento" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Visitante</label>
                    <select name="visitante" class="form-select" required>
                      <option value="">Seleccione un visitante</option>
                      <?php foreach ($visitantesC as $vv): ?>
                        <option value="<?= h($vv['id']) ?>"><?= h($vv['nombre']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
              </form>
            </div>
          </div>
        </div>

      <?php break; case 'perfil': ?>
        <div class="card shadow-sm col-12 col-md-8 col-lg-6">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-person-circle me-1"></i>Mi perfil</h5>
            <dl class="row mb-0">
              <dt class="col-sm-4">Nombre</dt><dd class="col-sm-8"><?= h($user['nombre'] ?? '-') ?></dd>
              <dt class="col-sm-4">Correo</dt><dd class="col-sm-8"><?= h($user['correo'] ?? ($user['email'] ?? '-')) ?></dd>
              <dt class="col-sm-4">Rol</dt><dd class="col-sm-8"><span class="badge text-bg-info">Empleado</span></dd>
            </dl>
          </div>
        </div>

      <?php break; case 'cambiar': ?>
        <div class="card shadow-sm col-12 col-md-8 col-lg-6">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-key me-1"></i>Cambiar contraseña</h5>
            <form method="post" action="<?= h($bp) ?>/panel/empleado?section=cambiar" class="mt-3">
              <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
              <div class="mb-3">
                <label class="form-label">Contraseña actual</label>
                <input type="password" class="form-control" name="actual" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Nueva contraseña</label>
                <input type="password" class="form-control" name="nueva" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirmar nueva contraseña</label>
                <input type="password" class="form-control" name="confirmar" required>
              </div>
              <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar</button>
            </form>
          </div>
        </div>

      <?php break; default: ?>
        <div class="alert alert-warning">Sección no encontrada.</div>
      <?php endswitch; ?>
    </main>
  </div>
</div>
<footer class="text-center text-muted small py-3 mt-4">&copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.</footer>
</body>
</html>
