<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$user = $_SESSION['user'] ?? null;
if (!$user || ($user['rol'] ?? '') !== 'empleado') {
    header('Location: ' . ($GLOBALS['basePath'] ?? '') . '/login'); exit;
}
$pdo = isset($pdo) && $pdo instanceof PDO ? $pdo : (require __DIR__ . '/../../src/Config/database.php');
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
$bp = $GLOBALS['basePath'] ?? '';
if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
$section = $_GET['section'] ?? 'dashboard';
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
</head>
<body class="bg-gray-100">
<div class="container-fluid">
  <div class="row">
  <nav class="col-md-3 col-lg-2 d-md-block py-4 px-2 sidebar">
      <div class="text-center mb-4">
        <span class="fw-bold fs-5">VisitaSegura</span>
        <div class="small mt-1"><?= h($user['nombre']) ?> <span class="badge bg-info">Empleado</span></div>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item mb-2"><a class="nav-link<?= $section==='dashboard'?' active':'' ?>" style="color:#fff" href="?section=dashboard">Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link<?= $section==='visitas'?' active':'' ?>" style="color:#fff" href="?section=visitas">Visitas</a></li>
        <li class="nav-item mb-2"><a class="nav-link<?= $section==='perfil'?' active':'' ?>" style="color:#fff" href="?section=perfil">Perfil</a></li>
        <li class="nav-item mb-2"><a class="nav-link<?= $section==='cambiar'?' active':'' ?>" style="color:#fff" href="?section=cambiar">Cambiar contraseña</a></li>
        <li class="nav-item mt-4"><a class="nav-link text-danger" href="<?= h($bp) ?>/logout">Cerrar sesión</a></li>
      </ul>
    </nav>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <?php include __DIR__ . '/partials/ui/toasts.php'; ?>
      <?php
        $labelsHdr = [ 'dashboard'=>'Dashboard', 'visitas'=>'Visitas', 'perfil'=>'Perfil', 'cambiar'=>'Cambiar contraseña' ];
        $pageTitle = $labelsHdr[$section] ?? 'Panel';
        $breadcrumbs = [ ['label' => $pageTitle] ];
        $showSearch = in_array($section, ['visitas'], true);
        include __DIR__ . '/partials/ui/header.php';
      ?>
      <?php
      switch ($section) {
        case 'dashboard':
          $totalVisitas = (int)$pdo->query("SELECT COUNT(*) FROM visitas")->fetchColumn();
          $totalPend = (int)$pdo->query("SELECT COUNT(*) FROM visitas WHERE estado='pendiente'")->fetchColumn();
          echo '<h2>Dashboard</h2>
          <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card text-bg-primary"><div class="card-body"><h5 class="card-title">Visitas</h5><p class="fs-3 mb-0">'.$totalVisitas.'</p></div></div></div>
            <div class="col-md-4"><div class="card text-bg-warning"><div class="card-body"><h5 class="card-title">Pendientes</h5><p class="fs-3 mb-0">'.$totalPend.'</p></div></div></div>
          </div>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="?section=visitas"><i class="bi bi-list-ul me-1"></i> Ir a Visitas</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearVisitante"><i class="bi bi-person-plus me-1"></i> Nuevo visitante</button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearVisita"><i class="bi bi-calendar-plus me-1"></i> Nueva visita</button>
          </div>';
          break;
        case 'visitas':
          $visitas = $pdo->query("SELECT * FROM visitas ORDER BY fecha DESC")->fetchAll();
          $ids = array_column($visitas, 'visitante_id');
          $documentos = [];
          if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $st = $pdo->prepare("SELECT id, documento FROM visitantes WHERE id IN ($in)");
            $st->execute($ids);
            foreach ($st->fetchAll() as $r) $documentos[$r['id']] = $r['documento'];
          }
       echo '<div class="d-flex gap-2 align-items-center mb-3">'
         .'<h2 class="m-0">Visitas</h2>'
         .'<div class="ms-auto d-flex gap-2">'
         .'<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearVisitante"><i class="bi bi-person-plus me-1"></i> Nuevo visitante</button>'
         .'<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearVisita"><i class="bi bi-calendar-plus me-1"></i> Nueva visita</button>'
         .'</div></div>';
  echo '<div class="table-responsive"><table class="table table-striped table-bordered table-hover align-middle bg-white shadow-sm content-card">';
          echo '<thead class="table-light"><tr><th>ID</th><th>Documento</th><th>Fecha</th><th>Salida</th><th>Motivo</th><th>Departamento</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';
          foreach ($visitas as $v) {
            echo '<tr>
              <td>'.h($v['id']).'</td>
              <td>'.h($documentos[$v['visitante_id']] ?? '-').'</td>
              <td>'.h($v['fecha']).'</td>
              <td>'.h($v['salida'] ?? '-').'</td>
              <td>'.h($v['motivo']).'</td>
              <td>'.h($v['departamento'] ?? '-').'</td>
              <td>'.
                (($v['estado'] ?? 'pendiente') === 'pendiente' ? '<span class="badge bg-warning text-dark">Pendiente</span>' :
                 (($v['estado'] ?? '') === 'autorizada' ? '<span class="badge bg-success">Autorizada</span>' :
                 (($v['estado'] ?? '') === 'rechazada' ? '<span class="badge bg-danger">Rechazada</span>' : '-')))
              .'</td>
              <td><div class="d-flex flex-wrap gap-1">';
          if (($v['estado'] ?? 'pendiente') === 'pendiente') {
           echo '<button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalAutorizarVisita"'
             .' data-id="'.h($v['id']).'" data-decision="autorizar"><i class="bi bi-check2-circle"></i> Autorizar</button>';
           echo '<button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalAutorizarVisita"'
             .' data-id="'.h($v['id']).'" data-decision="rechazar"><i class="bi bi-x-circle"></i> Rechazar</button>';
          }
          if (empty($v['salida'])) {
           echo '<button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalConfirmSalida"'
             .' data-id="'.h($v['id']).'"><i class="bi bi-box-arrow-right"></i> Marcar salida</button>';
          }
            echo '</div></td></tr>';
          }
          echo '</tbody></table></div>';
          break;
        case 'perfil':
          ?>
          <h2>Perfil</h2>
          <div class="card"><div class="card-body">
            <p><strong>Nombre:</strong> <?= h($user['nombre']) ?></p>
            <p><strong>Correo:</strong> <?= h($user['correo']) ?></p>
            <p><strong>Rol:</strong> <span class="badge bg-info">Empleado</span></p>
          </div></div>
          <?php
          break;
        case 'cambiar':
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf = $_POST['csrf_token'] ?? '';
            $actual = $_POST['actual'] ?? '';
            $nueva = $_POST['nueva'] ?? '';
            $confirmar = $_POST['confirmar'] ?? '';
            if ((!empty($_SESSION['csrf_token']) ? hash_equals($_SESSION['csrf_token'], $csrf) : true) && $nueva === $confirmar && strlen($nueva) >= 6) {
              $st = $pdo->prepare("SELECT hash_contrasena FROM usuarios WHERE id=?");
              $st->execute([$user['id']]);
              $row = $st->fetch();
              if ($row && password_verify($actual, $row['hash_contrasena'])) {
                $hash = password_hash($nueva, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE usuarios SET hash_contrasena=? WHERE id=?")->execute([$hash, $user['id']]);
                echo '<div class="alert alert-success">Contraseña actualizada.</div>';
              } else echo '<div class="alert alert-danger">La contraseña actual no es correcta.</div>';
            } else echo '<div class="alert alert-danger">Solicitud inválida o contraseñas no válidas.</div>';
          }
          ?>
          <h2>Cambiar contraseña</h2>
          <form method="post" action="?section=cambiar" class="col-md-6 col-lg-5">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
            <div class="mb-3"><label class="form-label">Contraseña actual</label><input type="password" name="actual" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Nueva contraseña</label><input type="password" name="nueva" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Confirmar nueva contraseña</label><input type="password" name="confirmar" class="form-control" required></div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
          </form>
          <?php
          break;
        default:
          echo '<h2>Bienvenido</h2>';
          break;
      }
      ?>

      <!-- Modal: Crear visitante -->
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

      <!-- Modal: Crear visita -->
      <div class="modal fade" id="modalCrearVisita" tabindex="-1" aria-hidden="true" aria-labelledby="modalCrearVisitaLabel">
        <div class="modal-dialog">
          <div class="modal-content">
            <?php $visitantesC = $pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre")->fetchAll(); ?>
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

  <?php include __DIR__ . '/partials/modals_visita_actions.php'; ?>

      <script>
  // Listeners de autorizar/salida ya están en el parcial incluido
      </script>
    </main>
  </div>
</div>
<footer class="text-center text-muted small py-3 mt-4">&copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.</footer>
</body>
</html>
