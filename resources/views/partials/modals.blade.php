<!-- Modal Confirmacao (unico modal) -->
<div id="modal-confirm" class="modal-overlay" style="display:none;">
    <div class="modal-container">
        <div class="modal-head"><h2 id="confirm-title">Confirmar</h2><button class="modal-x" onclick="closeConfirm()">&times;</button></div>
        <div class="modal-body"><p id="confirm-msg">Tem a certeza?</p></div>
        <div class="modal-foot">
            <button class="btn-ghost" onclick="closeConfirm()">Cancelar</button>
            <button class="btn-danger" id="confirm-btn" onclick="execConfirm()">Confirmar</button>
        </div>
    </div>
</div>