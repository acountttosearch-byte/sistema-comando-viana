<div id="section-ocorrencias" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Ocorrencias</h1><p class="page-desc">Registo e gestao de ocorrencias criminais</p></div>
        <button class="btn-primary" onclick="formNovaOcorrencia()"><i class='bx bx-plus'></i> Nova Ocorrencia</button>
    </div>

    <div class="filters">
        <select id="f-oc-estado" onchange="loadOcorrencias()"><option value="">Estado</option></select>
        <select id="f-oc-prioridade" onchange="loadOcorrencias()"><option value="">Prioridade</option><option value="baixa">Baixa</option><option value="media">Media</option><option value="alta">Alta</option><option value="critica">Critica</option></select>
        <select id="f-oc-tipo" onchange="loadOcorrencias()"><option value="">Tipo</option></select>
        <input type="date" id="f-oc-di" onchange="loadOcorrencias()">
        <input type="date" id="f-oc-df" onchange="loadOcorrencias()">
        <div class="search-filter"><i class='bx bx-search'></i><input type="text" id="f-oc-busca" placeholder="Buscar..."></div>
        <button class="btn-ghost" onclick="loadOcorrencias()"><i class='bx bx-filter-alt'></i></button>
    </div>

    <div class="tbl">
        <div class="tbl-head"><div class="col c2">N. Ocorrencia</div><div class="col c2">Tipo</div><div class="col c2">Local</div><div class="col c1">Data</div><div class="col c1">Prioridade</div><div class="col c1">Estado</div><div class="col c1">Accoes</div></div>
        <div id="list-oc"><div class="tbl-empty">Sem dados.</div></div>
    </div>
    <div id="pag-oc" class="pagination"></div>
</div>