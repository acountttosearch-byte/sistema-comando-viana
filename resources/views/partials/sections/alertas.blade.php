<div id="section-alertas" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Alertas / BOLO</h1><p class="page-desc">Avisos de procura e emergência</p></div>
        <button class="btn-danger" onclick="modalNovoAlerta()"><i class='bx bx-bell-plus'></i> Emitir Alerta</button>
    </div>
    <div class="tabs-bar">
        <button class="tab active" onclick="loadAlertas('activo',event)">Activos</button>
        <button class="tab" onclick="loadAlertas('resolvido',event)">Resolvidos</button>
        <button class="tab" onclick="loadAlertas('',event)">Todos</button>
    </div>
    <div id="list-alertas" class="alerts-list"><div class="tbl-empty">Sem alertas.</div></div>
</div>