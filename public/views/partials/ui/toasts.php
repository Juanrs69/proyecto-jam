<?php
// Parcial de toasts (Bootstrap 5) reutilizable.
// Muestra mensajes de la sesión (flash) y query ?ok=1 / ?err=...
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$bp = $GLOBALS['basePath'] ?? '';
$flashes = $_SESSION['flashes'] ?? [];
unset($_SESSION['flashes']);
$ok = isset($_GET['ok']) ? 'Operación realizada correctamente.' : '';
$err = $_GET['err'] ?? '';
?>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
  <div id="toastContainer" class="toast-container">
    <?php foreach ($flashes as $f): $type = $f['type'] ?? 'info'; $msg = $f['msg'] ?? ''; ?>
      <div class="toast align-items-center text-bg-<?= htmlspecialchars($type) ?> border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body"><?= htmlspecialchars($msg) ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if ($ok): ?>
      <div class="toast align-items-center text-bg-success border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body"><?= htmlspecialchars($ok) ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($err): ?>
      <div class="toast align-items-center text-bg-danger border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body"><?= htmlspecialchars($err) ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<script>
(function(){
  var elList = document.querySelectorAll('#toastContainer .toast');
  elList.forEach(function(el){
    var t = new bootstrap.Toast(el, { delay: 4000 });
    t.show();
  });
})();
</script>
