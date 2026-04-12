<div id="section-detencoes" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Detenções</h1><p class="page-desc">Registo e acompanhamento</p></div>
        <button class="btn-primary" onclick="formNovaDetencao()"><i class='bx bx-plus'></i> Nova Detenção</button>
    </div>
    <div class="filters">
        <div class="search-filter"><i class='bx bx-search'></i><input type="text" id="f-det-busca" placeholder="Buscar por nome, BI, nº detenção..."></div>
        <select id="f-det-estado" onchange="loadDetencoes()"><option value="">Estado</option></select>
        @if(in_array(auth()->user()->perfil->nome, ['admin', 'comandante']))
        <select id="f-det-unidade" onchange="loadDetencoes()"><option value="">Unidade</option></select>
        @endif
        <input type="date" id="f-det-di" onchange="loadDetencoes()">
        <input type="date" id="f-det-df" onchange="loadDetencoes()">
        <button class="btn-ghost" onclick="loadDetencoes()"><i class='bx bx-search'></i></button>
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c2">Nº Detenção</div><div class="col c2">Detido</div><div class="col c2">Ocorrência</div><div class="col c1">Data</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div>
        <div id="list-det"><div class="tbl-empty">Sem dados.</div></div>
    </div>
    <div id="pag-det" class="pagination"></div>
</div>