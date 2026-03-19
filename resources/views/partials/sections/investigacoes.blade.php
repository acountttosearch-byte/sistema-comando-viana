<div id="section-investigacoes" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Investigacoes</h1><p class="page-desc">Processos investigativos</p></div>
        <button class="btn-primary" onclick="formNovaInvestigacao()"><i class='bx bx-plus'></i> Nova Investigacao</button>
    </div>
    <div class="filters"><select id="f-inv-estado" onchange="loadInvestigacoes()"><option value="">Estado</option></select></div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c2">N. Investigacao</div><div class="col c1">Ocorrencia</div><div class="col c2">Investigador</div><div class="col c2">Progresso</div><div class="col c1">Estado</div><div class="col c1">Accoes</div></div>
        <div id="list-inv"><div class="tbl-empty">Sem dados.</div></div>
    </div>
</div>