<div id="section-mensagens" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Mensagens</h1><p class="page-desc">Comunicacao interna</p></div>
        <button class="btn-primary" onclick="formNovaMensagem()"><i class='bx bx-plus'></i> Nova</button>
    </div>
    <div class="tabs-bar">
        <button class="tab active" onclick="loadMensagens('inbox',event)">Recebidas</button>
        <button class="tab" onclick="loadMensagens('enviadas',event)">Enviadas</button>
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c0"></div><div class="col c2">De / Para</div><div class="col c3">Assunto</div><div class="col c1">Prioridade</div><div class="col c1">Data</div></div>
        <div id="list-msg"><div class="tbl-empty">Sem mensagens.</div></div>
    </div>
</div>