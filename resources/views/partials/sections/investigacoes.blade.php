<div id="section-investigacoes" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Investigações</h1><p class="page-desc">Processos investigativos</p></div>
        <button class="btn-primary" onclick="formNovaInvestigacao()"><i class='bx bx-plus'></i> Nova Investigação</button>
    </div>
    <div class="filters">
        <div class="search-filter"><i class='bx bx-search'></i><input type="text" id="f-inv-busca" placeholder="Buscar por nº investigação ou resumo..."><button class="btn-ghost btn-sm" onclick="loadInvestigacoes()" style="border:none;"><i class='bx bx-search'></i></button></div>
        <select id="f-inv-estado" onchange="loadInvestigacoes()"><option value="">Estado</option></select>
        <input type="date" id="f-inv-di" onchange="loadInvestigacoes()">
        <input type="date" id="f-inv-df" onchange="loadInvestigacoes()">
    </div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c2">Nº Investigação</div><div class="col c1">Ocorrência</div><div class="col c2">Investigador</div><div class="col c2">Progresso</div><div class="col c1">Estado</div><div class="col c1">Acções</div></div>
        <div id="list-inv"><div class="tbl-empty">Sem dados.</div></div>
    </div>
</div>