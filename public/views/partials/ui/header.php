<?php
// Parcial de cabecera con breadcrumb y búsqueda opcional
// Variables esperadas:
// $pageTitle (string)
// $breadcrumbs (array [['label'=>..., 'href'=>...], ...])
// $showSearch (bool)
if (!isset($pageTitle)) { $pageTitle = 'Panel'; }
if (!isset($breadcrumbs) || !is_array($breadcrumbs)) { $breadcrumbs = []; }
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
        <li class="breadcrumb-item <?= $last ? 'active' : '' ?>" <?= $last ? 'aria-current="page"' : '' ?>>
          <?php if (!$last && !empty($bc['href'])): ?>
            <a href="<?= htmlspecialchars($bc['href']) ?>"><?= htmlspecialchars($bc['label'] ?? '') ?></a>
          <?php else: ?>
            <?= htmlspecialchars($bc['label'] ?? '') ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  </nav>
  <h2 class="ms-3 mb-0"><?= htmlspecialchars($pageTitle) ?></h2>
  <?php if ($showSearch): ?>
    <div class="ms-auto">
      <input type="text" class="form-control" id="tableSearch" placeholder="Buscar...">
    </div>
  <?php endif; ?>
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
