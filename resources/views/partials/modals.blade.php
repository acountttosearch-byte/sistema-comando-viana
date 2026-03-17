<!-- Modal Principal -->
<div id="modal" class="modal-overlay" style="display:none;">
    <div class="modal-container">
        <div class="modal-head"><h2 id="modal-title">Título</h2><button class="modal-x" onclick="closeModal()">&times;</button></div>
        <div class="modal-body" id="modal-body"></div>
        <div class="modal-foot" id="modal-foot"></div>
    </div>
</div>

<!-- Modal Confirmação -->
<div id="modal-confirm" class="modal-overlay" style="display:none;">
    <div class="modal-container sm">
        <div class="modal-head"><h2 id="confirm-title">Confirmar</h2></div>
        <div class="modal-body"><p id="confirm-msg">Tem a certeza?</p></div>
        <div class="modal-foot">
            <button class="btn-ghost" onclick="closeConfirm()">Cancelar</button>
            <button class="btn-danger" id="confirm-btn" onclick="execConfirm()">Confirmar</button>
        </div>
    </div>
</div>

<!-- Painel lateral de detalhes -->
<div id="detail-overlay" class="detail-bg" style="display:none;" onclick="closeDetail()"></div>
<div id="detail-panel" class="detail-slide" style="display:none;">
    <div class="detail-head"><h3 id="detail-title">Detalhes</h3><button class="modal-x" onclick="closeDetail()">&times;</button></div>
    <div class="detail-body" id="detail-body"></div>
</div>