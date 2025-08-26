<?php
// Parcial de cabecera con breadcrumb y búsqueda opcional
// Variables esperadas:
// $pageTitle (string)
// $breadcrumbs (array [['label'=>..., 'href'=>...], ...])
// $showSearch (bool)
if (!isset($pageTitle)) { $pageTitle = 'Panel'; }
if (!isset($breadcrumbs) || !is_array($breadcrumbs)) { $breadcrumbs = []; }
// Normalizar: permitir items como string (se convierten a ['label'=>...])
$breadcrumbs = array_map(function($b){
  if (is_array($b)) return $b;
  return ['label' => (string)$b];
}, $breadcrumbs);
$showSearch = $showSearch ?? false;
// Home (Inicio) href: permitir override por $homeHref o auto según rol
$homeHref = $homeHref ?? null;
if ($homeHref === null) {
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  $bp = $GLOBALS['basePath'] ?? '';
  $rol = $_SESSION['user']['rol'] ?? '';
  if ($rol === 'empleado')      $homeHref = $bp . '/panel/empleado';
  elseif ($rol === 'recepcionista') $homeHref = $bp . '/panel/recepcionista';
  else                           $homeHref = $bp . '/panel';
}
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
  <?php
      // Notificaciones: mostrar campana con contador
      try {
    $pdoH = require __DIR__ . '/../../../../src/Config/database.php';
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        $uid = $_SESSION['user']['id'] ?? null;
        $bp = $GLOBALS['basePath'] ?? '';
        $count = 0;
        if ($uid) {
          $stH = $pdoH->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
          $stH->execute([$uid]);
          $count = (int)$stH->fetchColumn();
        }
      } catch (\Throwable $e) { $count = 0; $bp = $GLOBALS['basePath'] ?? ''; }
    ?>
    <a href="<?= htmlspecialchars($bp) ?>/notificaciones" class="position-relative btn btn-light">
      <i class="bi bi-bell"></i>
      <?php if ($count > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= (int)$count ?></span>
      <?php endif; ?>
    </a>
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
