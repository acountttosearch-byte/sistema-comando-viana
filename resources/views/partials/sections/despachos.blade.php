<div id="section-despachos" class="section">
    <div class="page-header">
        <div><h1 class="page-title">Despachos</h1><p class="page-desc">Atribuição de ocorrências</p></div>
        <button class="btn-primary" onclick="modalNovoDespacho()"><i class='bx bx-plus'></i> Novo Despacho</button>
    </div>
    <div class="filters"><select id="f-desp-estado" onchange="loadDespachos()"><option value="">Todos</option><option value="pendente">Pendentes</option><option value="aceite">Aceites</option><option value="em_curso">Em Curso</option><option value="concluido">Concluídos</option></select></div>
    <div class="tbl">
        <div class="tbl-head"><div class="col c2">Ocorrência</div><div class="col c1">Prioridade</div><div class="col c2">Para</div><div class="col c1">Unidade</div><div class="col c1">Estado</div><div class="col c1">Data</div><div class="col c1">Acções</div></div>
        <div id="list-desp"><div class="tbl-empty">Sem dados.</div></div>
    </div>
</div>