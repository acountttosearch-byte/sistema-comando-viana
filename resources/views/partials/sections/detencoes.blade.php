<div id="section-detencoes" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Detenções</h1><p class="page-desc">Registo e acompanhamento</p></div>
        <button class="btn-primary" onclick="modalNovaDetencao()"><i class='bx bx-plus'></i> Nova Detenção</button>
    </div>
    <div class="filters">
        <select id="f-det-estado" onchange="loadDetencoes()"><option value="">Estado</option></select>
        <input type="date" id="f-det-di" onchange="loadDetencoes()">
        <input type="date" id="f-det-df" onchange="loadDetencoes()">
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c2">Nº Detenção</div><div class="col c2">Detido</div><div class="col c2">Ocorrência</div><div class="col c1">Data</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div>
        <div id="list-det"><div class="tbl-empty">Sem dados.</div></div>
    </div>
    <div id="pag-det" class="pagination"></div>
</div>