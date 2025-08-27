<?php
// Parcial de cabecera con breadcrumb y búsqueda opcional
// Variables esperadas:
// $pageTitle (string)
// $breadcrumbs (array [['label'=>..., 'href'=>...], ...])
// $showSearch (bool)

// Título por defecto
if (!isset($pageTitle)) { $pageTitle = 'Panel'; }

// Normalizar migas
if (!isset($breadcrumbs) || !is_array($breadcrumbs)) { $breadcrumbs = []; }
$breadcrumbs = array_map(function($b){
  if (is_array($b)) return $b;
  return ['label' => (string)$b];
}, $breadcrumbs);

// Mostrar buscador (opcional)
$showSearch = $showSearch ?? false;

// Enlace Inicio según rol (sobrescribible con $homeHref)
$homeHref = $homeHref ?? null;
if ($homeHref === null) {
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  $bp = $GLOBALS['basePath'] ?? '';
  $sessUser = $_SESSION['user'] ?? null;
  $rol = is_array($sessUser) ? ($sessUser['rol'] ?? '') : '';
  if ($rol === 'empleado')         $homeHref = $bp . '/panel/empleado';
  elseif ($rol === 'recepcionista') $homeHref = $bp . '/panel/recepcionista';
  else                              $homeHref = $bp . '/panel';
}

// Contador de notificaciones
try {
  $pdoH = require __DIR__ . '/../../../../src/Config/database.php';
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  $sessUser2 = $_SESSION['user'] ?? null;
  $uid = is_array($sessUser2) ? ($sessUser2['id'] ?? null) : null;
  $bp = $GLOBALS['basePath'] ?? '';
  $count = 0;
  if ($uid) {
    $stH = $pdoH->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL');
    $stH->execute([$uid]);
    $count = (int)$stH->fetchColumn();
  }
} catch (\Throwable $e) { $count = 0; $bp = $GLOBALS['basePath'] ?? ''; }
?>

<div class="d-flex align-items-center mb-3">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars($homeHref) ?>">Inicio</a></li>
      <?php foreach ($breadcrumbs as $i => $bc): $last = $i === array_key_last($breadcrumbs); ?>
        <?php $bcLabel = is_array($bc) ? ($bc['label'] ?? '') : (string)$bc; $bcHref = is_array($bc) ? ($bc['href'] ?? null) : null; ?>
        <li class="breadcrumb-item <?= $last ? 'active' : '' ?>" <?= $last ? 'aria-current="page"' : '' ?>>
          <?php if (!$last && !empty($bcHref)): ?>
            <a href="<?= htmlspecialchars($bcHref) ?>"><?= htmlspecialchars($bcLabel) ?></a>
          <?php else: ?>
            <?= htmlspecialchars($bcLabel) ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  </nav>
  <h2 class="ms-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
  <div class="ms-auto d-flex align-items-center gap-3">
    <button type="button" class="position-relative btn btn-light" data-bs-toggle="modal" data-bs-target="#modalNotificaciones">
      <i class="bi bi-bell"></i>
      <?php if ($count > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= (int)$count ?></span>
      <?php endif; ?>
    </button>
    <?php if ($showSearch): ?>
    <div class="ms-auto">
      <input type="text" class="form-control" id="tableSearch" placeholder="Buscar...">
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  var input = document.getElementById('tableSearch');
  if (!input) return;
  input.addEventListener('input', function(){
    var term = (this.value || '').toLowerCase();
    var table = document.querySelector('table');
    if (!table) return;
    var rows = table.querySelectorAll('tbody tr');
    rows.forEach(function(row){
      var text = row.textContent.toLowerCase();
      row.style.display = text.indexOf(term) !== -1 ? '' : 'none';
    });
  });
})();
</script>

<!-- Modal: Notificaciones -->
<div class="modal fade" id="modalNotificaciones" tabindex="-1" aria-hidden="true" aria-labelledby="modalNotificacionesLabel">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNotificacionesLabel"><i class="bi bi-bell-fill me-1"></i> Notificaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <?php
          // Cargar últimas notificaciones para el usuario actual
          $listUnread = $listRead = [];
          try {
            if (!isset($pdoH) || !($pdoH instanceof PDO)) {
              $pdoH = require __DIR__ . '/../../../../src/Config/database.php';
            }
            $uH = $_SESSION['user'] ?? null; $uidH = is_array($uH) ? ($uH['id'] ?? null) : null;
            if ($uidH) {
              $stU = $pdoH->prepare('SELECT * FROM notifications WHERE user_id = ? AND read_at IS NULL ORDER BY created_at DESC LIMIT 50');
              $stU->execute([$uidH]);
              $listUnread = $stU->fetchAll();
              $stR = $pdoH->prepare('SELECT * FROM notifications WHERE user_id = ? AND read_at IS NOT NULL ORDER BY created_at DESC LIMIT 100');
              $stR->execute([$uidH]);
              $listRead = $stR->fetchAll();
            }
          } catch (\Throwable $e) { $listUnread = $listRead = []; }
        ?>
        <div class="row g-4">
          <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
              <div class="card-header bg-primary text-white"><i class="bi bi-bell-fill me-1"></i> No leídas</div>
              <ul class="list-group list-group-flush">
                <?php if ($listUnread): foreach ($listUnread as $n): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                      <div class="fw-semibold"><?= htmlspecialchars($n['title'] ?? '') ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($n['created_at'] ?? '') ?></div>
                      <div><?= htmlspecialchars($n['body'] ?? '') ?></div>
                    </div>
                    <form method="post" action="<?= htmlspecialchars($GLOBALS['basePath'] ?? '') ?>/notificaciones/<?= urlencode($n['id']) ?>/leer">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Marcar como leída"><i class="bi bi-check2"></i></button>
                    </form>
                  </li>
                <?php endforeach; else: ?>
                  <li class="list-group-item">Sin notificaciones nuevas.</li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
          <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
              <div class="card-header"><i class="bi bi-inbox me-1"></i> Historial</div>
              <ul class="list-group list-group-flush">
                <?php if ($listRead): foreach ($listRead as $n): ?>
                  <li class="list-group-item">
                    <div class="fw-semibold"><?= htmlspecialchars($n['title'] ?? '') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($n['created_at'] ?? '') ?></div>
                    <div><?= htmlspecialchars($n['body'] ?? '') ?></div>
                  </li>
                <?php endforeach; else: ?>
                  <li class="list-group-item">Sin historial.</li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <form method="post" action="<?= htmlspecialchars($GLOBALS['basePath'] ?? '') ?>/notificaciones/leer-todas" class="ms-auto" onsubmit="return confirm('¿Marcar todas como leídas?');">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <button type="submit" class="btn btn-outline-primary"><i class="bi bi-check2-all me-1"></i> Marcar todas como leídas</button>
        </form>
      </div>
    </div>
  </div>
</div>
