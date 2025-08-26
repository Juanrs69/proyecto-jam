<?php
// Parcial: Modales de acciones sobre Visitas (autorizar/rechazar y salida)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$bp = $GLOBALS['basePath'] ?? '';
?>

<!-- Modal Autorizar/Rechazar visita (POST con CSRF) -->
<div class="modal fade" id="modalAutorizarVisita" tabindex="-1" aria-hidden="true" aria-labelledby="modalAutorizarVisitaLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="formAutorizarVisita">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAutorizarVisitaLabel">Confirmar decisión</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="decision" id="authDecision" value="">
          <p id="authPregunta" class="m-0"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Confirmar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Confirmar salida (POST con CSRF) -->
<div class="modal fade" id="modalConfirmSalida" tabindex="-1" aria-hidden="true" aria-labelledby="modalConfirmSalidaLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="formSalidaVisita">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConfirmSalidaLabel">Confirmar salida</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body"><p>¿Marcar salida ahora mismo para esta visita?</p></div>
        <div class="modal-footer">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-right me-1"></i> Marcar salida</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Autorizar / Rechazar
(function(){
  var modalAutorizar = document.getElementById('modalAutorizarVisita');
  if (!modalAutorizar) return;
  modalAutorizar.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var id = button.getAttribute('data-id');
    var decision = button.getAttribute('data-decision');
    var pregunta = decision === 'autorizar' ? '¿Deseas autorizar esta visita?' : '¿Deseas rechazar esta visita?';
    document.getElementById('authPregunta').textContent = pregunta;
    var form = document.getElementById('formAutorizarVisita');
    form.action = '<?= htmlspecialchars($bp) ?>/visits/' + encodeURIComponent(id) + '/authorize';
    var decisionInput = document.getElementById('authDecision');
    if (decisionInput) decisionInput.value = decision;
  });
})();

// Marcar salida
(function(){
  var modalSalida = document.getElementById('modalConfirmSalida');
  if (!modalSalida) return;
  modalSalida.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var id = button.getAttribute('data-id');
    var form = document.getElementById('formSalidaVisita');
    form.action = '<?= htmlspecialchars($bp) ?>/visits/' + encodeURIComponent(id) + '/exit';
  });
})();
</script>
